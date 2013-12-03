<?php
class Message {
    static private $messages;
    
    static function add($msg,$type='info') {
        if( !isset(Message::$messages) ) {
            Message::$messages = array();
        }

        if( !isset(Message::$messages[$type]) ) {
            Message::$messages[$type] = array();
        }

        if( array_search($msg,Message::$messages[$type]) === false ) {
            Message::$messages[$type][]=$msg;
        }
    }

    static function addSession($msg,$type='info') {

        if( !isset($_SESSION['messages']) ) {
            $_SESSION['messages'] = array();
        }
        
        if( !isset($_SESSION['messages'][$_SESSION["call"]]) ) {
            $_SESSION['messages'][$_SESSION["call"]] = array();
        }

        if( !isset($_SESSION['messages'][$_SESSION["call"]][$type]) ) {
            $_SESSION['messages'][$_SESSION["call"]][$type] = array();
        }

        if( array_search($msg,$_SESSION['messages'][$_SESSION["call"]][$type]) === false ) {
            $_SESSION['messages'][$_SESSION["call"]][$type][]=$msg;
        }
    }

    static function display() {
        $output = "";

        if( isset(Message::$messages) ) {
            foreach( Message::$messages as $key=>$value ) {
                $output.= Render::messages($value,$key);
            }
        }

        if( isset($_SESSION['messages']) && isset($_SESSION["call"]) && isset($_SESSION['messages'][$_SESSION["call"]-1]) ) {
            foreach( $_SESSION['messages'][$_SESSION["call"]-1] as $key=>$value ) {
                $output.= Render::messages($value,$key);
            }
        }

        return $output;
    }

    static function init() {
        if( isset($_GET['msg']) ) {
            Message::add($_GET['msg']);
        }
    }
}
?>