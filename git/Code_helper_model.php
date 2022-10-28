<?php
# [AUTHOR] darin prodanov
# [EMAIL] d.prodanov@jarcomputers.com
# [CREATED] 12/01/2022
# [MODIFIED] 15/06/2022
final class Code_helper_model extends J_Model
{
	public function getStaff()
	{
		$query = '
		SELECT
			"Име", "Съдружник ID", "Position ID"
		FROM
			public."Съдружници"
		WHERE
			"Active" = true AND "Position ID" NOT IN (501, 701, 702, 710, 1001)
			--501[Касиер]
			--701[Стажант счетоводител]
			--702[Счетоводител]
			--710[Главен счетоводител]
			--1001[Хигиенист]
		ORDER BY
			"Име" ASC
		';

		$result = $this->db->query($query)->result_array();

		$staff = [
			'Програмисти' => [],
			'Други Служители' => []
		];

		foreach ($result as $row)
		{
			$type = in_array($row['Position ID'], [1011, 103]) ? 'Програмисти' : 'Други Служители';

			$staff[$type][$row['Съдружник ID']] = $row['Име'];
		}

		return $staff;
	}

	public function setLanguageTable()
	{
		$language = [
			'plaintext' => 'текст',
			'link' => 'линк',
			'sql' => 'SQL',
			'shell' => 'Shell',
			'bash' => 'Bash',
			'php' => 'PHP',
			'cpp' => 'C++',
			'csharp' => 'C#',
			'ruby' => 'Ruby',
			'perl' => 'Perl',
			'python' => 'Python',
			'java' => 'Java',
			'vbnet' => 'Visual Basic',
			'html' => 'HTML',
			'xml' => 'XML',
			'css' => 'CSS',
			'javascript' => 'JavaScript',
			'json' => 'JSON'
		];

		$languages = [];

		foreach ($language as $key => $value)
		{
			$languages[] = "('{$key}', '{$value}')";
		}

		$languages = implode(',' . PHP_EOL, $languages);

		$language_table = "
		SELECT
			*
		FROM
			(
				VALUES
				{$languages}
			) AS sub(lang, lang_name)
		";

		return $language_table;
	}

	public function getLanguage()
	{
		$result = $this->db->query($this->setLanguageTable())->result_array();

		$language = [];

		foreach ($result as $row)
		{
			$language[$row['lang']] = $row['lang_name'];
		}

		return $language;
	}

	public function getCodeByID($id)
	{
		$query = '
		SELECT
			sub.id, sub.body, sub.code, sub.lang, sub.created_by, sub.right, sub.viewed_by_id, st."Име" AS viewed_by
		FROM
			(
				SELECT
					he.id, he.body, he.code, he.lang,
					UNNEST(CASE
					WHEN he.viewed_by != \'{}\' THEN he.viewed_by
					ELSE \'{null}\'
					END) AS viewed_by_id, st."Име" AS created_by,
					(CASE
					WHEN he.user_id = ' . $this->session->uid . ' THEN \'write\'
					ELSE \'read\'
					END) AS "right"
				FROM public.helpers AS he
				JOIN public."Съдружници" AS st ON he.user_id = st."Съдружник ID"
				WHERE he.id = ' . $id . ' AND (he.user_id = ' . $this->session->uid . ' OR ' . $this->session->uid . ' = ANY(he.viewed_by))
			) AS sub
		LEFT JOIN public."Съдружници" AS st ON sub.viewed_by_id::int = st."Съдружник ID"
		GROUP BY sub.id, sub.body, sub.code, sub.lang, sub.created_by, sub.right, sub.viewed_by_id, st."Име"
		';

		$result = $this->db->query($query)->result_array();

		$code = [];

		foreach ($result as $row)
		{
			if (!array_key_exists($row['id'], $code))
			{
				$code[$row['id']] = [
					'body' => $row['body'],
					'code' => $row['code'],
					'lang' => $row['lang'],
					'right' => $row['right'],
					'created_by' => $row['created_by'],
					'viewed_by' => []
				];
			}

			if (!empty($row['viewed_by_id']))
			{
				$code[$row['id']]['viewed_by'][$row['viewed_by_id']] = $row['viewed_by'];
			}
		}

		return $code[$row['id']];
	}

	public function getCode()
	{
		$query = '
		SELECT sub.*, viewed.viewed_by::varchar
		FROM
			(
				SELECT \'write\' AS "right", he.id, he.body, he.code, he.lang, he.created, la.lang_name, st."Име" AS created_by
				FROM public.helpers AS he
				JOIN (' . $this->setLanguageTable() . ') AS la ON he.lang = la.lang
				JOIN public."Съдружници" AS st ON he.user_id = st."Съдружник ID"
				WHERE he.user_id = ' . $this->session->uid . '
			) AS sub
		LEFT JOIN
			(
				SELECT sub.id, JSON_AGG(JSON_BUILD_OBJECT(sub.viewed_by_id, st."Име")) AS viewed_by
				FROM (SELECT id, UNNEST(viewed_by) AS viewed_by_id FROM public.helpers WHERE user_id = ' . $this->session->uid . ') AS sub
				JOIN public."Съдружници" AS st ON sub.viewed_by_id = st."Съдружник ID"
				GROUP BY sub.id
			) AS viewed USING(id)
		UNION
		SELECT sub.*, viewed.viewed_by::varchar
		FROM
			(
				SELECT viewed.id, JSON_AGG(JSON_BUILD_OBJECT(viewed.viewed_by_id, st."Име")) AS viewed_by from
				(
					SELECT sub.id, UNNEST(sub.viewed_by) AS viewed_by_id
					FROM (SELECT id, viewed_by, UNNEST(viewed_by) AS viewed_by_id FROM public.helpers) AS sub
					WHERE sub.viewed_by_id = ' . $this->session->uid . '
				) AS viewed
				JOIN public."Съдружници" AS st ON viewed.viewed_by_id = st."Съдружник ID"
				GROUP BY viewed.id
			) AS viewed
		JOIN
			(
				SELECT \'read\' AS "right", he.id, he.body, he.code, he.lang, he.created, la.lang_name, st."Име" AS created_by
				FROM public.helpers AS he
				JOIN (' . $this->setLanguageTable() . ') AS la ON he.lang = la.lang
				JOIN public."Съдружници" AS st ON he.user_id = st."Съдружник ID"
			) AS sub ON viewed.id = sub.id
		';

		$result = $this->db->query($query)->result_array();

		$code = [];

		foreach ($result as $row)
		{
			$array = json_decode($row['viewed_by'], true);

			$viewed_by = [];

			foreach ($array as $value)
			{
				$viewed_by[key($value)] = current($value);
			}

			$code[$row['id']] = [
				'right' => $row['right'],
				'body' => $row['body'],
				'code' => $row['code'],
				'lang' => $row['lang'],
				'lang_name' => $row['lang_name'],
				'created' => $row['created'],
				'created_by' => $row['created_by'],
				'viewed_by' => $viewed_by
			];
		}

		return $code;
	}

	public function setCode($id, $data)
	{
		if (is_null($id))
		{
			$this->db->set('user_id', $this->session->uid, false);
			$this->db->set('created', 'NOW()::timestamp(0)', false);

			$result = $this->db->insert('public.helpers', $data);
			$row_id = $this->db->insert_id();
		}
		else
		{
			$result = $this->db->update('public.helpers', $data, ['id' => $id, 'user_id' => intval($this->session->uid)]);
			$row_id = $id;
		}

		if ($result)
		{
			return $row_id;
		}

		return false;
	}

	public function unsetCode($id)
	{
		$query = "
		DELETE FROM
			public.helpers
		WHERE
			id = {$id} AND user_id = {$this->session->uid}
		";

		$result = $this->db->query($query);

		return $result;
	}

	public function setInquiry($body, $code, $is_create_inquiry)
	{
		if ($is_create_inquiry)
		{
			$query = '
			INSERT INTO
				public."TСправки" ("Текст", tsql)
			VALUES
				(\'' . $body . '\', \'' . $code . '\')
			';

			$this->db->query($query);

			$row_id = $this->db->insert_id();

			$query = '
			INSERT INTO
				public."TСправкиПозволения" ("ID", "UserID")
			VALUES
				(' . $row_id . ', ' . $this->session->uid . ')
			';

			$result = $this->db->query($query);
		}
		else
		{
			$query = '
			UPDATE
				public."TСправки"
			SET
				"Текст" = \'TEMP \' || \'(' . $body . ')\',
				tsql = \'' . $code . '\'
			WHERE
				"ID" = -1
			';

			$row_id = -1;

			$result = $this->db->query($query);
		}

		if ($result)
		{
			return $row_id;
		}
	}
}
?>