{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* @filesource frmInner.tpl *}
{* Purpose: smarty template - inner frame for workarea *}
<!DOCTYPE html>
<head>
  <title>打印信息</title>
</head>
<?php
    $wuziling = $_COOKIE['args'];
    echo $wuziling;
?>
<frameset>
<p>而且热群若</p>
<p>{$wuziling}</p>

</frameset>
</html>
