<?php

namespace Module\Admin;



class ctlIndex extends ctlBase
{
    public function funcIndex()
    {
        $this->needLogin();
        $this->display('index.html');
    }
}