<?php
if(isset($login_uri))
	redirect($login_uri);
$this->view('denied');
