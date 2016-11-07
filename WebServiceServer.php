<?php
		require_once "lib/nusoap.php";
		$server= new nusoap_server();
		$server->configureWSDL("BOOKSTORE","urn:book");
		$server->wsdl->addComplexType("ArrayOfString", 
						 "complexType", 
						 "array", 
						 "", 
						 "SOAP-ENC:Array", 
						 array(), 
						 array(array("ref"=>"SOAP-ENC:arrayType","wsdl:arrayType"=>"xsd:string[]")), 
						 "xsd:string"); 
		$server->register("findBook",
						array("keyword" => "xsd:string"),
						array("return" =>  "tns:ArrayOfString")
						);
		function findBook($keyword){
			$xml = simplexml_load_file('BookStore.xml');
			$result = array();
			foreach ($xml->book as $book) {
				$category = (string) $book['category'];
				$title = (String) $book->title;
				$author = (String) $book->author;
				$price = (int) $book->price;
				$page = (int) $book->page;
				$price_b = (int) $book->price_bath;
				$oper = substr($keyword, 0,1);
				$KeyPrice = (int)substr($keyword, 1,strlen($keyword));
				if($oper == '>' || $oper == '<'||$oper == '='){
					if($oper == '>'){
						if($page >=  $KeyPrice){
							array_push($result,$book['category'],$book->title,$book->author,$book->publisher,$book->publish_date,$book->price,$book->page,$book->price_bath);
						}
					}elseif($oper == '<'){
						if($page <=  $KeyPrice){
							array_push($result,$book['category'],$book->title,$book->author,$book->publisher,$book->publish_date,$book->price,$book->page,$book->price_bath);
						}
					}elseif($oper == '='){
						if($page ==  $KeyPrice){
							array_push($result,$book['category'],$book->title,$book->author,$book->publisher,$book->publish_date,$book->price,$book->page,$book->price_bath);
						}
					}
					
				}elseif(strncasecmp($category ,$keyword , strlen($keyword)) == 0 || (strncasecmp($title ,$keyword , strlen($keyword)) == 0) || (strncasecmp($author ,$keyword , strlen($keyword)) == 0)){
					array_push($result,$book['category'],$book->title,$book->author,$book->publisher,$book->publish_date,$book->price,$book->page,$book->price_bath);
				}
			}  
			return $result;
		}
		// Register Add Function
		$addVar = array(
			'titleVar'=>'xsd:string',
			'authorVar'=>'xsd:string',
			'publisherVar'=>'xsd:string',
			'publish_dateVar'=>'xsd:string',
			'typeVar'=>'xsd:string',
			'languageVar'=>'xsd:string',
			'priceVar'=>'xsd:string',
			'page'=>'xsd:string',
			'price_b'=>'xsd:string'	
			);
		$server->register(
			'AddXML',
			$addVar,
			array('return'=>'xsd:string')
			);
		function AddXML($titleVar,$authorVar,$publisherVar,$publish_dateVar,$typeVar,$languageVar,$priceVar,$page,$price_b){
			$file = 'BookStore.xml';
			$xml = simplexml_load_file($file);

			$book = $xml->addChild('book');
			$book->addAttribute('category', 'new');
			$book->addChild('title', $titleVar);
			$book->title->addAttribute('lang', 'en');
			$book->addChild('author', $authorVar);
			$book->addChild('publisher', $publisherVar);
			$book->addChild('publish_date', $publish_dateVar);
			$book->addChild('type', $typeVar);
			$book->addChild('language',$languageVar);
			$book->addChild('price',$priceVar);
			$book->addChild('page',$page);
			$book->addChild('price_bath',$price_b);
			$xml->asXML($file);	
			
			return "Add (name) <b>$titleVar</b> Success";
		}
		
		// Register Edit Function 
		$editVar = array(
			'from_name'=>'xsd:string',
			'to_name'=>'xsd:string'
			);
		$server->register(
			'EditXML',
			$editVar,
			array('return'=>'xsd:string')
			);
        function EditXML($from_name, $to_name) {			
			$xmlStr = file_get_contents('BookStore.xml'); 
			$xml = new SimpleXMLElement($xmlStr);
			$book = $xml->book;
			for($j=0;$j<sizeof($book);$j++){
				foreach ($book[$j] as $key => $value) {
					if($from_name==$value and $key=="title")
						$book[$j]->title = $to_name;
				}
			}			
			$output = $xml->asXML('BookStore.xml');		
			return "Edit Done ! (from) <b>$from_name</b> (to) <b>$to_name</b>";
		}
		 
		// Register Delete Function 
		$server->register(
			'DeleteXML',
			array('mark_name'=>'xsd:string'),
			array('return'=>'xsd:string')
			);
        function DeleteXML($mark_name) {
        	$name = $mark_name;			
			$xmlStr = file_get_contents('BookStore.xml'); 
			$xml = new SimpleXMLElement($xmlStr);
			$book = $xml->book;
			for($k=0;$k<sizeof($book);$k++){
				foreach ($book[$k] as $key => $value) {
					if($mark_name==$value and $key=="title"){
						$dom=dom_import_simplexml($book[$k]);
						$dom->parentNode->removeChild($dom);
						// MAY NOT USE 'unset' bcoz it will be not show the 'string' that we are returning.
						// unset($book[$k]);
					}
				}
			}			
			$output = $xml->asXML('BookStore.xml');		
			return "Delete (name) <b>$mark_name</b> Success!";
		} 

		// Get our posted data if the service is being consumed
		// otherwise leave this data blank.
		$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
		 
		// pass our posted data (or nothing) to the soap service
		$server->service($POST_DATA);
		//exit(); 
?>
