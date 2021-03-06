<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
if (!defined("IN_BAIGO")) {
    exit("Access Denied");
}

include_once(BG_PATH_CLASS . "tpl.class.php"); //载入模板类
include_once(BG_PATH_MODEL . "call.class.php");
include_once(BG_PATH_MODEL . "cate.class.php");
include_once(BG_PATH_MODEL . "mark.class.php");
include_once(BG_PATH_MODEL . "spec.class.php");
include_once(BG_PATH_MODEL . "thumb.class.php");

/*-------------用户类-------------*/
class CONTROL_CALL {

    public $obj_tpl;
    public $mdl_call;
    public $adminLogged;
    private $is_super = false;

    function __construct() { //构造函数
        $this->obj_base       = $GLOBALS["obj_base"];
        $this->config         = $this->obj_base->config;
        $this->obj_dir        = new CLASS_DIR();
        $this->adminLogged    = $GLOBALS["adminLogged"];
        $_arr_cfg["admin"] = true;
        $this->obj_tpl        = new CLASS_TPL(BG_PATH_TPLSYS . "admin/" . BG_DEFAULT_UI, $_arr_cfg); //初始化视图对象
        $this->mdl_call       = new MODEL_CALL();
        $this->mdl_cate       = new MODEL_CATE();
        $this->mdl_mark       = new MODEL_MARK();
        $this->mdl_spec       = new MODEL_SPEC();
        $this->tplData = array(
            "adminLogged" => $this->adminLogged
        );

        if ($this->adminLogged["admin_type"] == "super") {
            $this->is_super = true;
        }

        $this->group_allow = $this->adminLogged["groupRow"]["group_allow"];
    }


    function ctl_show() {
        if (!isset($this->group_allow["call"]["browse"]) && !$this->is_super) {
            return array(
                "alert" => "x170303",
            );
        }

        $_num_callId    = fn_getSafe(fn_get("call_id"), "int", 0);
        $_arr_specRows  = array();

        if ($_num_callId < 1) {
            return array(
                "alert" => "x170213"
            );
        }

        $_arr_callRow = $this->mdl_call->mdl_read($_num_callId);
        if ($_arr_callRow["alert"] != "y170102") {
            return $_arr_callRow;
        }

        $_arr_searchCate = array(
            "status" => "show",
        );
        $_arr_cateRows  = $this->mdl_cate->mdl_list(1000, 0, $_arr_searchCate);
        $_arr_markRows  = $this->mdl_mark->mdl_list(100);
        if ($_arr_callRow["call_spec_ids"]) {
            $_arr_searchSpec = array(
                "spec_ids"    => $_arr_callRow["call_spec_ids"],
            );
            $_arr_specRows = $this->mdl_spec->mdl_list(1000, 0, $_arr_searchSpec);
        }

        $_arr_tpl = array(
            "callRow"    => $_arr_callRow,
            "cateRows"   => $_arr_cateRows,
            "markRows"   => $_arr_markRows,
            "specRows"   => $_arr_specRows,
        );

        $_arr_tplData = array_merge($this->tplData, $_arr_tpl);

        $this->obj_tpl->tplDisplay("call_show.tpl", $_arr_tplData);

        return array(
            "alert" => "y170102",
        );
    }


    /**
     * ctl_form function.
     *
     * @access public
     * @return void
     */
    function ctl_form() {
        $_num_callId    = fn_getSafe(fn_get("call_id"), "int", 0);
        $_arr_specRows  = array();

        if ($_num_callId > 0) {
            if (!isset($this->group_allow["call"]["edit"]) && !$this->is_super) {
                return array(
                    "alert" => "x170303",
                );
            }
            $_arr_callRow = $this->mdl_call->mdl_read($_num_callId);
            if ($_arr_callRow["alert"] != "y170102") {
                return $_arr_callRow;
            }

            if ($_arr_callRow["call_spec_ids"]) {
                $_arr_searchSpec = array(
                    "spec_ids"    => $_arr_callRow["call_spec_ids"],
                );
                $_arr_specRows = $this->mdl_spec->mdl_list(1000, 0, $_arr_searchSpec);
            }
            //print_r($_arr_callRow);
        } else {
            if (!isset($this->group_allow["call"]["add"]) && !$this->is_super) {
                return array(
                    "alert" => "x170302",
                );
            }
            $_arr_callRow = array(
                "call_id"           => 0,
                "call_name"         => "",
                "call_file"         => "html",
                "call_tpl"          => "",
                "call_amount"       => array(
                    "top"       => 10,
                    "except"    => 0,
                ),
                "call_attach"       => "",
                "call_cate_id"      => "",
                "call_cate_ids"     => array(),
                "call_cate_excepts" => array(),
                "call_mark_ids"     => array(),
                //"call_spec_ids"     => array(),
                "call_type"         => "",
                "call_status"       => "enable",
            );
        }

        $_arr_searchCate = array(
            "status" => "show",
        );

        $_arr_cateRows    = $this->mdl_cate->mdl_list(1000, 0, $_arr_searchCate);
        $_arr_markRows    = $this->mdl_mark->mdl_list(100);

        $_arr_tplRows     = $this->obj_dir->list_dir(BG_PATH_TPL . "call/");

        $_arr_tpl = array(
            "specRows"   => $_arr_specRows,
            "callRow"    => $_arr_callRow,
            "cateRows"   => $_arr_cateRows,
            "markRows"   => $_arr_markRows,
            "tplRows"    => $_arr_tplRows,
        );

        $_arr_tplData = array_merge($this->tplData, $_arr_tpl);

        $this->obj_tpl->tplDisplay("call_form.tpl", $_arr_tplData);

        return array(
            "alert" => "y170102",
        );
    }


    /**
     * ctl_list function.
     *
     * @access public
     * @return void
     */
    function ctl_list() {
        if (!isset($this->group_allow["call"]["browse"]) && !$this->is_super) {
            return array(
                "alert" => "x170301",
            );
        }

        $_arr_search = array(
            "key"        => fn_getSafe(fn_get("key"), "txt", ""),
            "type"       => fn_getSafe(fn_get("type"), "txt", ""),
            "status"     => fn_getSafe(fn_get("status"), "txt", ""),
        );

        $_num_callCount   = $this->mdl_call->mdl_count($_arr_search);
        $_arr_page        = fn_page($_num_callCount); //取得分页数据
        $_str_query       = http_build_query($_arr_search);
        $_arr_callRows    = $this->mdl_call->mdl_list(BG_DEFAULT_PERPAGE, $_arr_page["except"], $_arr_search);

        $_arr_tpl = array(
            "query"      => $_str_query,
            "pageRow"    => $_arr_page,
            "search"     => $_arr_search,
            "callRows"   => $_arr_callRows, //管理员列表
        );

        $_arr_tplData = array_merge($this->tplData, $_arr_tpl);

        $this->obj_tpl->tplDisplay("call_list.tpl", $_arr_tplData);

        return array(
            "alert" => "y170301",
        );

    }
}
