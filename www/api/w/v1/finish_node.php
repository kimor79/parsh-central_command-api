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

if(!$parsh->validateJobID($_POST['job_id'])) {
	print($ops->formatWriteOutput('400', 'Invalid job id'));
	exit(0);
}

$job_id = $_POST['job_id'];

if(!$parsh->validateToken($_POST['token'], $job_id)) {
	print($ops->formatWriteOutput('400', 'Invalid token'));
	exit(0);
}

if(!$parsh->validateNode($_POST['node'])) {
	print($ops->formatWriteOutput('400', 'Invalid node'));
	exit(0);
}

$node = mysql_real_escape_string($_POST['node']);

$stdout = mysql_real_escape_string($_POST['stdout']);
$stderr = mysql_real_escape_string($_POST['stderr']);
$exit = mysql_real_escape_string($_POST['exit_status']);
$error = mysql_real_escape_string($_POST['error_message']);

$time = time();
$query = sprintf("UPDATE `nodes` SET `finish_time`='%d', `stdout`='%s', `stderr`='%s',
	`exit_status`='%s', `error_message`='%s' WHERE `job_id`='%d' AND `node`='%s'",
	$time, $stdout, $stderr, $exit, $error, $job_id, $node);
$result = do_mysql_query($query);
if($result[0] === true) {
	if(mysql_affected_rows() > 0) {
		$details = array('node' => $node, 'time' => $time);
		print($ops->formatWriteOutput('200', 'Node finished', $details));
		exit(0);
	}

	print($ops->formatWriteOutput('400', 'Node is not part of this job'));
	exit(0);
}

print($ops->formatWriteOutput('500', $result[1]));
exit(0);
?>
