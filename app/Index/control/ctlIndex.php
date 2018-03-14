<?php


namespace Module\Index;


class ctlIndex extends ctlBase
{
    public function funcIndex(){
        $this->display("base.html", ['name' => 'bobby']);
    }
}