<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
if(!defined("IN_BAIGO")) {
	exit("Access Denied");
}

include_once(BG_PATH_FUNC . "admin.func.php"); //载入模板类
include_once(BG_PATH_CLASS . "tpl.class.php"); //载入模板类
include_once(BG_PATH_MODEL . "admin.class.php"); //载入管理帐号模型

/*-------------管理员控制器-------------*/
class CONTROL_LOGON {

	private $obj_base;
	private $config; //配置
	private $obj_tpl;
	private $mdl_admin;
	private $tplData;

	function __construct() { //构造函数
		$this->obj_base   = $GLOBALS["obj_base"]; //获取界面类型
		$this->config     = $this->obj_base->config;
		$this->mdl_admin  = new MODEL_ADMIN(); //设置管理员模型
	}

	/**
	 * ctl_login function.
	 *
	 * @access public
	 * @return void
	 */
	function ctl_login() {
		$_arr_adminLogin = fn_adminLogin();
		if ($_arr_adminLogin["str_alert"] != "ok") {
			return $_arr_adminLogin;
			exit;
		}

		$_arr_adminRow = $this->mdl_admin->mdl_loginChk($_arr_adminLogin["admin_name"], "admin_name");
		if ($_arr_adminRow["str_alert"] != "y020102") {
			return $_arr_adminRow;
			exit;
		}

		if (fn_baigoEncrypt($_arr_adminLogin["admin_pass"], $_arr_adminRow["admin_rand"]) != $_arr_adminRow["admin_pass"]) {
			return array(
				"forward"   => $_arr_adminLogin["forward"],
				"str_alert" => "x020207",
			);
			exit;
		}

		if ($_arr_adminRow["admin_status"] != "enable") {
			return array(
				"forward"   => $_arr_adminLogin["forward"],
				"str_alert" => "x020402",
			);
			exit;
		}

		$_str_adminRand = fn_rand(6);

		$this->mdl_admin->mdl_loginSubmit($_arr_adminRow["admin_id"], fn_baigoEncrypt($_arr_adminLogin["admin_pass"], $_str_adminRand), $_str_adminRand);

		$_SESSION["admin_id_" . BG_SITE_SSIN]         = $_arr_adminRow["admin_id"];
		$_SESSION["admin_ssintime_" . BG_SITE_SSIN]   = time();
		$_SESSION["admin_hash_" . BG_SITE_SSIN]       = fn_baigoEncrypt($_arr_adminRow["admin_time"], $_str_adminRand);

		return array(
			"admin_id"   => $_arr_adminLogin["admin_id"],
			"forward"    => $_arr_adminLogin["forward"],
			"str_alert"  => "y020201",
		);
	}

	/*============登出============
	无返回
	*/
	function ctl_logout() {
		$_str_forward  = fn_getSafe($_GET["forward"], "txt", "");
		if (!$_str_forward) {
			$_str_forward = base64_encode(BG_URL_ADMIN . "admin.php");
		}

		fn_adminEnd();

		return array(
			"forward" => $_str_forward,
		);
	}

	/*============登录界面============
	无返回
	*/
	function ctl_logon() {
		$this->obj_tpl    = new CLASS_TPL(BG_PATH_TPL_ADMIN . $this->config["ui"]);
		$_str_forward     = fn_getSafe($_GET["forward"], "txt", "");

		$this->tplData = array(
			"forward" => $_str_forward,
		);

		$this->obj_tpl->tplDisplay("logon.tpl", $this->tplData);
	}
}
?>