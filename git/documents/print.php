<!doctype html>
<html>
<head>
<title>Принтирай Документ</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<style>
html, body, img {margin:0; padding:0; border:0;}
body {background:#000000;}
img {height:100%; margin:auto; position:absolute; top:0; right:0; bottom:0; left:0; display:block;}
</style>
<img src="<?= $image ?>">
<script>
window.print();
</script>
</body>
</html>