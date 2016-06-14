<?php
/**
 * BookValidationBooksprint_ext extension
 *
 * @file
 * @ingroup Extensions 
 */

class BookValidation {

    function __construct($bookTitleStr){
        $this->hasErrors = false;
        $this->results = array();
        $parts = explode("/", $bookTitleStr);
        $this->version = null;
        if (sizeof($parts) == 2){
            $bookTitleStr = $parts[0];
            $this->version = $parts[1];
        }
        $this->bookTitleStr = $bookTitleStr;
        $this->title =Title::newFromText($bookTitleStr);

        $v = $this->validateBookPageExists();
        if($v) $v = $this->validateBookPageInBookCategory();
        if($v)  $this->validateBookInfo();

    }

    function addResult($name, $res=null){
        $this->results[$name] = array();
        if (isset($res['errors'])){
            $this->hasErrors = true;
            foreach($res['errors'] as $e){
                $this->results[$name]['errors'] = $e;
            }
        }
        return !isset($res['errors']);
    }

    function validateBookPageExists(){
        $this->bookPage = WikiPage::factory($this->title);
        $this->bookPageText = $this->bookPage->getText(Revision::FOR_PUBLIC);
        $res = array();
        if($this->bookPageText==""){
            $res['errors'][] = "Die Seite $this->title existiert nicht";
        }
        return $this->addResult('page_exists',$res); 
    }

    function validateBookPageInBookCategory(){
        $categoryTree = $this->title->getParentCategoryTree();
        $categories = array_keys($categoryTree);
        $isBook = in_array('Kategorie:Buch', $categories);
        $res = array();
        if(! $isBook){
            $res['errors'][] = "Die Seite $this->title ist nicht in der Kategorie 'Buch'";
        }
        return $this->addResult('in_book_category', $res);
    }

    function validateBookInfo(){
        $text = $this->bookPageText;
        $templateStart =  "{{Bookinfo";
        $found = stripos($text,$templateStart);
        $res = array();
        if($found === false){
            $res['errors'][] = "Kein 'Bookinfo'-Template gefunden";
            return $this->addResult('has_info', $res);
        }
        $startPos = $found  + strlen($templateStart);
        $this->bookInfos = array();

        $endPos = stripos($text, "}}");
        $templ =  substr($text,$startPos , $endPos-$startPos);
        while(true){
            $delimPos = strpos($templ, "|");
            if($delimPos === false){
                break;
            }
            $templ = substr($templ, $delimPos+1);
            $nextDelimPos = strpos($templ, "|");
            $kv = $nextDelimPos===False ? $templ : substr($templ,0, $nextDelimPos);
            $ar = explode("=", $kv);
            if (sizeof($ar) != 2){
                continue;
            }
            $this->bookInfos[trim($ar[0])] = trim($ar[1]);
        }
        if(sizeof($this->bookInfos) == 0){
            $res['errors'] = "Keine Infos im Bookinfo-Template gefunden";
        }
        return $this->addResult('has_info', $res);
    }
}
