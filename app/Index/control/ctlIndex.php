<?php


namespace Module\Index;


class ctlIndex extends ctlBase
{
    public function funcIndex(){
        $this->display("index.html", ['name' => 'bobby']);
    }
}