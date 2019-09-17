<?php
/**
 * Created by PhpStorm.
 * User: MR.Z < zsh2088@gmail.com >
 * Date: 2017/9/21
 * Time: 15:49
 */
 namespace Smart\Controllers\Backend;


 use Facades\Smart\Service\ServiceManager;
use Smart\Service\SysFuncPrivilegeService;
use Smart\Service\SysFuncService;
use Smart\Service\SysRolePermissionService;
use Smart\Service\SysRoleService;
use Illuminate\Http\Request;
use Smart\Service\SysUserService;

class MerRole extends Backend {

    /**
     * SysRole constructor.
     */
    protected $autoload_service = false;

    public $controller = 'SysRole';

    public function __construct(Request $request){
        parent::__construct($request);
        $this->service = ServiceManager::make( \Smart\Service\SysRoleService::class );
        $jsCode = <<<EOF
            {$this->controller}.init();
EOF;

            $this->_addJsCode($jsCode);
    }

    //页面入口
    public function index(Request $request) {
        $this->_init( '机构角色管理' );
    //    $sysUserService = ServiceManager::make(SysUserService::class);
    //    dd($sysUserService->hasAnyPermission(2,[ 88]));
    //   dd( $sysUserService->permissions(2));
    //    $sysRoleService = ServiceManager::make(SysRoleService::class );
     //   dd($sysRoleService->permissions(3));

        //uri
        $this->_addParam( 'uri', [
            'getPermission'    => full_uri( 'backend/merrole/getpermission' ),
            'getPrivilegeData' => full_uri( 'backend/merrole/getprivilegeData'),
            'updatePermission' => full_uri( 'backend/merrole/updatepermission' )
        ] );

        $modules = explode(',',config('backend.module_ext'));
        $modules = array_combine($modules, $modules);

        //查询参数
        $this->_addParam( 'query', [
            'keyword'  => $request->input( 'keyword', '' ),
            'status'   => $request->input( 'status', '' ),
            'page'     => $request->input( 'page', 1 ),
            'pageSize' => $request->input( 'pageSize', 10 ),
            'sort'     => $request->input( 'sort', 'id' ),
            'order'    => $request->input( 'order', 'DESC' ),
        ] );


        //其他参数
        $this->_addParam( [
            'defaultRow' => $this->service->getDefaultRow(),
            'status'     => $this->service->status,
            'rank'       => $this->service->rank,
            'modules'    => $modules,   
        ] );

        //需要引入的 css 和 js
        $this->_addJsLib( 'static/plugins/dmg-ui/TableGrid.js' );


        return $this->_displayWithLayout('backend::merrole.index');
    }

    //读取
    function read(Request $request) {
        $param = [
            'status'   => $request->input( 'status', '' ),
            'keyword'  => $request->input( 'keyword', '' ),
            'page'     => $request->input( 'page', 1 ),
            'pageSize' => $request->input( 'pageSize', 10 ),
            'sort'     => $request->input( 'sort', 'id' ),
            'order'    => $request->input( 'order', 'DESC' ),
            'module'   => $request->input( 'module', '' ),
        ];

        $data['rows']   = $this->service->getByCond( $param );
        $param['count'] = TRUE;
        $data['total']  = $this->service->getByCond( $param );

        return json( ajax_arr( '查询成功', 0, $data ) );
    }

    /**
     * 新建
     *
     * @return \Json
     */
    public function insert(Request $request) {
        $data = $request->except( '_token' );
        $data['module'] = $request->module;

        return json( $this->service->insert( $data ) );
    }

    

    function get_privilegeData(Request $request){
        $roleId = $request->input( 'roleId' );
        $SysRolePermission    = SysRolePermissionService::instance();
        return response()->json($SysRolePermission->getByRole( $roleId ));
    }

    //更新授权
    function update_permission(Request $request) {
        $roleId       = $request->input( 'roleId' );
        $privilegeArr = $request->input( 'privilegeArr' );

        $SysRolePermission = SysRolePermissionService::instance();
        $ret               = $SysRolePermission->updateRolePermission( $roleId, $privilegeArr );

        return json( $ret );
    }

    //得到permission配置页面 非 js 渲染    v2 
    public function getPermission(Request $request){
        $params = [
            'module' => $request->module ?: 'backend',
        ];
        $sysFuncs = $this->service->getPermission($params); 
        
        return $this->_displayWithLayout('backend::merrole.permission1')->with('funcData',$sysFuncs);
    }

    //更新授权
    function updatePermission(Request $request) {
        $roleId = $request->input('roleId');
        $privilegeArr = $request->input('privilegeArr');

        $sysRoleService = ServiceManager::make(SysRoleService::class);
        $ret = $sysRoleService->updateRolePermission($roleId, $privilegeArr);

        return api_result('success',0);
    }

    function getPrivilegeData(Request $request) {
        $roleId = $request->input('roleId');
        $sysRoleService = ServiceManager::make(SysRoleService::class);
        return $sysRoleService->getByRole($roleId);
    }


}