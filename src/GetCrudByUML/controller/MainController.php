<?php


namespace GetCrudByUML\controller;


use GetCrudByUML\view\HomeView;

class MainController{


    public static function main(){
        
        $view = new HomeView();
        echo $view->head();
        echo $view->body();
    }

}
?>
