<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @package Codeigniter
 * @subpackage Email
 * @category Config
 * @author Agung Dirgantara <agungmasda29@gmail.com>
 */

$config['useragent'] = 'Skripsi';
$config['protocol'] = 'smtp';
$config['smtp_name'] = 'Pemko Medan';
$config['smtp_host'] = 'mail.pemkomedan.my.id';
$config['smtp_user'] = 'no-reply@pemkomedan.my.id';
$config['smtp_pass'] = 'no-reply';
$config['smtp_port'] = 465;
$config['smtp_crypto'] = 'ssl';
$config['wordwrap'] = TRUE;
$config['validate'] = FALSE;
$config['priority'] = 3;
$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['smtp_timeout'] = 10;
$config['smtp_keepalive'] = FALSE;

/* End of file email.php */
/* Location : ./application/config/email.php */
