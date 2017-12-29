<html>
<head>
<script>
<?php if($this->input->get('callback') !== null){
	echo $this->security->xss_clean($this->input->get('callback'))."();\r\n";
}
if($this->input->get('_modalIns') !== null){
	echo "parent.hc.ui.openModal.instances['".$this->security->xss_clean($this->input->get('_modalIns'))."'].modal('hide');\r\n";
}else{ 
	echo "window.close();";
 } ?>
</script>
</head>
<body>
</body>
</html>