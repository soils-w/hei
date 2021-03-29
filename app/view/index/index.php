<!DOCTYPE html>
<html>
<head>
    <title>view视图调用测试</title>
</head>
<style type="text/css">
    table, tr, th, td {
        border: 1px solid #000;
        border-collapse:collapse;
    }
</style>
<body>
<table>
    <tr>
        <th>用户名</th>
        <th>手机号</th>
    </tr>
    <?php foreach($rs as $key=>$value) { ?>
        <tr>
            <td><? echo $value['user_name']; ?></td>
            <td><? echo $value['mobile']; ?></td>
        </tr>
    <?php } ?>
</table>
</body>
</html>