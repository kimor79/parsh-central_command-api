<?php

/**

Copyright (c) 2010, Kimo Rosenbaum and contributors
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the owner nor the names of its contributors
      may be used to endorse or promote products derived from this
      software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

**/

include('Parsh/includes/rw.inc');

$argv = '';
$command = '';
$files = '';
$job_id = 0;
$parallel = 1;
$query_opts = array();

$nodes = array();
if(isset($_POST['nodes'])) {
	// TODO: validate nodes
	if(is_array($_POST['nodes'])) {
		$nodes = $_POST['nodes'];
	} else {
		$nodes = explode(',', $_POST['nodes']);
	}
}

if(empty($nodes)) {
	print($ops->formatWriteOutput('400', 'No nodes given'));
	exit(0);
}

if(isset($_POST['command'])) {
	$command = (get_magic_quotes_gpc()) ? stripslashes($_POST['command']) : $_POST['command'];
	$query_opts[] = sprintf("`command`='%s'", mysql_real_escape_string($command));
}

if(isset($_POST['files'])) {
	if(is_array($_POST['files'])) {
		$files = implode(' ', $_POST['files']);
	} else {
		$files = $_POST['files'];
	}

	$query_opts[] = sprintf("`files`='%s'", mysql_real_escape_string($files));
}

if(empty($command) && empty($files)) {
	print($ops->formatWriteOutput('400', 'No command or files given'));
	exit(0);
}

$query_opts[] = sprintf("`real_user`='%s'", mysql_real_escape_string($_SERVER['REMOTE_USER']));

if(!$ops->isBlank($_POST['run_as_user'])) {
	$query_opts[] = sprintf("`run_as_user`='%s'", mysql_real_escape_string($_POST['run_as_user']));
} else {
	$query_opts[] = sprintf("`run_as_user`='%s'", mysql_real_escape_string($_SERVER['REMOTE_USER']));
}

if(isset($_POST['parallel']) && ctype_digit((string)$_POST['parallel'])) {
	$parallel = $_POST['parallel'];
	if($parallel == 0) {
		$parallel = 1;
	}
}

$num_nodes = count($nodes);
if($num_nodes < $parallel) {
	$parallel = $num_nodes;
}

$query_opts[] = sprintf("`parallel`='%d'", mysql_real_escape_string($parallel));

$argv = '';
if(isset($_POST['argv'])) {
	$argv = (get_magic_quotes_gpc()) ? stripslashes($_POST['argv']) : $_POST['argv'];
	$query_opts[] = sprintf("`argv`='%s'", mysql_real_escape_string($argv));
}

$query_opts[] = sprintf("`run_from_node`='%s'", mysql_real_escape_string($_SERVER['REMOTE_ADDR']));
$query_opts[] = sprintf("`start_time`='%d'", time());

$query = 'INSERT INTO `jobs` SET ';
$query .= implode(', ', $query_opts);
$result = do_mysql_query($query);

if($result[0] === true) {
	$job_id = mysql_insert_id();
} else {
	print($ops->formatWriteOutput('500', $result[1]));
	exit(0);
}

if(!$parsh->validateJobID($job_id)) {
	print($ops->formatWriteOutput('500', 'Error generating job id'));
	exit(0);
}

$token = $parsh->generateToken($job_id);
if(!$parsh->validateToken($token, $job_id)) {
	print($ops->formatWriteOutput('500', 'Error generating job token'));
	exit(0);
}

$node_query = "INSERT INTO `nodes` (`job_id`, `node`) VALUES ";
$node_a_query = array();
foreach($nodes as $node) {
	$node_a_query[] = sprintf("('%d', '%s')", $job_id, $node);
}
$node_query .= implode(', ', $node_a_query);
$node_result = do_mysql_query($node_query);

if($node_result[0] === true) {
	if(mysql_affected_rows() != $num_nodes) {
		print($ops->formatWriteOutput('500', 'Error adding nodes to db'));
		exit(0);
	}
} else {
	print($ops->formatWriteOutput('500', $node_result[1]));
	exit(0);
}

$details = $parsh->getJobDetailsByJob(array('job_id' => $job_id));
$details = reset($details);
if(!empty($details)) {
	$details['token'] = $token;
	print($ops->formatWriteOutput('200', 'Job started', $details));
	exit(0);
}

print($ops->formatWriteOutput('500', 'Error returning job details'));
?>
