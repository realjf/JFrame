<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2017/11/21
 * Time: 15:24
 */

namespace Core\JFrame;


class Template
{
    protected $__options = [];
    /**
     * @var array \Twig_Environment
     */
    protected $__loader;
    private $__data = [];
    public $defaultPerPage = 20;

    public function __construct()
    {
        $this->defaultPerPage = \clsVars::get('n')->toInt(20, 1, 200);
    }

    /**
     * @param $key
     * @param $val
     */
    public function setOption($key, $val)
    {
        $this->__options[$key] = $val;
    }

    /**
     * @param array $data
     */
    public function setOptions(array $data)
    {
        foreach ($data as $k => $v){
            $this->__options[$k] = $v;
        }
    }

    public function assign($var, $value = NULL)
    {
        if(is_array($var) || is_object($var)){
            foreach($var as $name => $value){
                $this->__data[$name] = $value;
            }
        } else{
            $this->__data[$var] = $value;
        }
        return TRUE;
    }

    /**
     * @param $count
     * @param $perPage
     * @param $page
     */
    public function setPaginator($count, $perPage, $page)
    {
        $page = max($page, 1);
        $paginator = \clsTools::multi($count, $perPage, $page);
        $this->__data['G_Paginator'] = [
            'PageNums'  => $paginator['pagecount'],
            'Nums'      => $count,
            'Perpage'   => $paginator['perpage'],
            'Page'      => $paginator['page'],
            'Start'     => $paginator['start'],
            'HasPrev'   => $paginator['hasprev'],
            'HasNext'   => $paginator['hasnext'],
            'PageLink'  => $paginator['pagelink'],
            'Pages'     => $this->_getPages($paginator['pagecount'], $page, $paginator['pagelink']),
            'PageLinkPrev' => $paginator['pagelink'] . (max(1, $page - 1)),
            'PageLinkNext' => $paginator['pagelink'] . (min($paginator['pagecount'], $page+1)),
        ];
    }

    private function _getPages($pageNums, $page, $pageLink)
    {
        $pages = [];
        if ($page >= $pageNums - 4 && $pageNums > 9){
            $start = $pageNums - 9 + 1;
            $length = 9;
        }elseif($page >= 5 && $pageNums > 9){
            $start = $page - 5 + 1;
            $length = min(9, $page + 5);
        }else{
            $start = 1;
            $length = min(9, $pageNums);
        }
        for($p = 0; $p < $length; $p++){
            $pp = $start + $p;
            $pages[] = [
                'IsActive' => $pp == $page ? 1 : 0,
                'PageLink' => $pageLink . ($pp),
                'Page'  => $pp,
            ];
        }
        return $pages;
    }

    public function display($tpl, $data = [], $tplDir = '')
    {
        $theme = Config::instance()->read('app.theme');
        $tplDir = $tplDir ?:  JFRAME_APP_PATH . "/" . ucfirst($this->__options['module']) . '/template/' . $theme;
        $loader = new \Twig_Loader_Filesystem($tplDir);
        $this->__loader[$theme] = new \Twig_Environment($loader, array('debug' => Config::instance()->read('app.debug')));
        try{
            $data['G_Module'] = $this->__options['module'];
            $data['G_Method'] = $this->__options['method'];
            $data['G_Control'] = $this->__options['control'];
            $data['G_URI']  = $this->__options['uri'];
            foreach($this->__data as $key => $val){
                $data[$key] = $val;
            }
            $this->__loader[$theme]->display($tpl, $data);
        }catch (\Exception $e){
            var_dump($e->getMessage());
            exit();
        }
    }

    public function output($codeMsg = '', $result = NULL)
    {
        $codes = $codeMsg ? explode(':', $codeMsg) : [];
        $code = $codes[0] ?: 100;
        $msg = $codes[1] ?: '';
        $response = new \clsResponse();
        $response->response($code, $result, $msg);
    }

    public function response($code, $result = NULL, $msg = '') {
        $response = new \clsResponse();
        $response->response($code, $result, $msg);
    }


    public function funcIndex()
    {
        echo "ok";
    }
}