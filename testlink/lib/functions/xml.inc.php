<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * support for XML
 * 
 * @package   TestLink
 * @copyright   2004-2013, TestLink community 
 * @filesource  xml.inc.php
 * @link    http://www.teamst.org/index.php
 *
 * @internal revisions
 * @since 1.9.9
 * 20131013 - franciscom - added simplexml_load_file_wrapper()
 *                         After user contribution regarding XML External Entity (XXE) Processing Attacks
 */


/**
 * Use this sample file to test attack (user contribution)
 * <?xml version='1.0' encoding='UTF-8'?><!DOCTYPE foo [<!ENTITY xxe SYSTEM "file:///etc/passwd"> ]>
 * <platforms>
 *  <platform>
 *   <name>&xxe;</name>
 *  </platform>
 * </platforms>
 *
 */
function simplexml_load_file_wrapper($filename)
{
  // http://websec.io/2012/08/27/Preventing-XXE-in-PHP.html
  libxml_disable_entity_loader(true);  
  $zebra = file_get_contents($filename);
  $xml = @simplexml_load_string($zebra);
  return $xml;
}


/**
 * 
 *
 */
function exportDataToXML($items,$rootTpl,$elemTpl,$elemInfo,$bNoXMLHeader = false)
{
  if (!$items)
  {
    return;
  }

  $xmlCode = '';
  reset($items);
  while($item = each($items))
  {
    $item = $item[1];
    $xmlElemCode = $elemTpl;
    
    // REMEMBER YOU NEED TO USE XMP TO DEBUG
    // echo '$xmlElemCode'; echo "<xmp>$xmlElemCode)</xmp>";
    foreach($elemInfo as $subject => $replacement)
    {
      $fm = substr($subject,0,2);
      $content = isset($item[$replacement]) ? $item[$replacement] : null;
      
      switch($fm)
      {
        case '||':
        break;

        case '{{':
        default:
          $content = htmlspecialchars($content);
        break;
      }
      
      $howMany = 0;
	  
	  
      $xmlElemCode = str_replace($subject,$content,$xmlElemCode,$howMany);
	  
    }
  

    $xmlCode .= $xmlElemCode;
	
  }
  reset($items);
  
  $result = null;
  if (!$bNoXMLHeader)
  {
    $result .= TL_XMLEXPORT_HEADER."\n";
  }
 
  if($rootTpl != '' && !is_null($rootTpl))
  {  
    $result .= str_replace("{{XMLCODE}}",$xmlCode,$rootTpl);
    return $result;
  } 
  else
  {
    return $xmlCode;
	
  } 
}

/**
 * 
 *
 */
function exportDataToWORD($items,$rootTpl,$elemTpl,$elemInfo,$bNoXMLHeader = false)
{
  if (!$items)
  {
    return;
  }

  $xmlCode = '';
  reset($items);
  while($item = each($items))
  {
    $item = $item[1];
    $xmlElemCode = $elemTpl;
    
    // REMEMBER YOU NEED TO USE XMP TO DEBUG
    // echo '$xmlElemCode'; echo "<xmp>$xmlElemCode)</xmp>";
    foreach($elemInfo as $subject => $replacement)
    {
      $fm = substr($subject,0,2);
      $content = isset($item[$replacement]) ? $item[$replacement] : null;
      
      switch($fm)
      {
        case '||':
        break;

        case '{{':
        default:
          $content = htmlspecialchars($content);
        break;
      }
      
      $howMany = 0;
	  
	  
		if(strlen(trim($content))<1) {
			if($subject=="||EQUIPMENT||") {
				$xmlElemCode = str_replace($subject,"",$xmlElemCode,$howMany);
			} elseif ($subject=="||EXPECTEDRESULTS||") {
				$xmlElemCode = str_replace($subject,"",$xmlElemCode,$howMany);
			} elseif ($subject=="||NOTES||") {
				$xmlElemCode = str_replace($subject,"",$xmlElemCode,$howMany);
			} else {
				$xmlElemCode = str_replace($subject,$content,$xmlElemCode,$howMany);
			}
		} else {
			if($subject=="||EQUIPMENT||") {
				$stepTemplate1="\t".'<p align="left"><font size="2" face="Calibri" color="green">Operation equipment:||EQUIPMENT||</p>'."\n" ;
				$xmlElemCode = str_replace($subject,$stepTemplate1,$xmlElemCode,$howMany);
			} elseif ($subject=="||EXPECTEDRESULTS||") {
				$stepTemplate1="\t".'<p align="left"><font size="2" face="Calibri" color="black"><span style="color:#4ce73c">Expected:</span></p>'."\n\t".'<p align="left"><font size="2" face="Calibri" color="black">&emsp;&emsp;&emsp;&emsp;||EXPECTEDRESULTS||</p>'."\n" ;	
				$xmlElemCode = str_replace($subject,$stepTemplate1,$xmlElemCode,$howMany);
			} elseif ($subject=="||NOTES||") {
				$stepTemplate1="\t".'<p align="left"><font size="2" face="Calibri" color="yellow">&emsp;||NOTES||</p></div>';
				$xmlElemCode = str_replace($subject,$stepTemplate1,$xmlElemCode,$howMany);
			}
			$xmlElemCode = str_replace($subject,$content,$xmlElemCode,$howMany);
			
		}
	  
    
	  
    }
  

    $xmlCode .= $xmlElemCode;
	
  }
  reset($items);
  
  $result = null;
  if (!$bNoXMLHeader)
  {
    $result .= TL_XMLEXPORT_HEADER."\n";
  }
 
  if($rootTpl != '' && !is_null($rootTpl))
  {  
    $result .= str_replace("{{XMLCODE}}",$xmlCode,$rootTpl);
    return $result;
  } 
  else
  {
    return $xmlCode;
	
  } 
}

function getautomationtcsteps() {
	@include_once('config_db.inc.php');
	if( !defined('DB_HOST') ) {
		define('DB_HOST','localhost' );
	}


	
	$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

	mysqli_query($mysqli,"SET NAMES utf8");

	if ($mysqli->connect_error) {
		die('Connect Error (' . $mysqli->connect_errno . ') '
				. $mysqli->connect_error);
	}
	
	$sql= "select id as automation_id,notes as automation_notes from automation_tcsteps;";
	
	$result =$mysqli->query($sql);
	$array=$result->fetch_all();
	$data = array();
	for($k=0;$k<count($array);$k++)
	{
		$arr=$array[$k];
		$data[$arr[0]] = $arr[1];
	}
	return $data;
	
	
}
/**
 * $simpleXMLItems
 * $itemStructure: keys elements, attributes
 *
 *           both keys are maps:
 *           key: element/attribute type
 *           value: map 
 *              key: attribute name 
 *              value: options used to request special
 *                   processing like trim(), intval(),etc.
 *                
 *           Example:
 *           $tcXML['elements'] = array('string' => array("summary" => null,
 *                                "preconditions" => 'trim'),
 *                        'integer' => array("node_order" => 'intval',
 *                                 "externalid" => null,
 *                                                     "execution_type" => null,
 *                                                     "importance" => null));
 *
 *           $tcXML['attributes'] = array('string' => array("name" => 'trim'), 
 *                                              'integer' => array('internalid' => null));
 *  
 *                            
 */
function getItemsFromSimpleXMLObj($simpleXMLItems,$itemStructure)
{
  $items = null;
  if($simpleXMLItems)
  {
    $items_counter=0;
    $loop_qty = count($simpleXMLItems);

    // new dBug($loop_qty);
    for($idx=0; $idx < $loop_qty; $idx++)
    {
      // echo "DEBUG - " . __FUNCTION__ . " \$idx:$idx<br>";
      foreach($itemStructure['elements'] as $castType => $keyValues)
      {
        foreach($keyValues as $key => $fn2apply)
        {
          $dummy[$key] = null;
          if( property_exists($simpleXMLItems[$idx],$key) )
          {
            $dummy[$key] = $simpleXMLItems[$idx]->$key;
            settype($dummy[$key],$castType);
            if(!is_null($fn2apply))
            {
              $dummy[$key] = $fn2apply($dummy[$key]);
            }   
          }
        }
      } 

      if( isset($itemStructure['attributes']) && !is_null($itemStructure['attributes']) )
      {
        foreach($itemStructure['attributes'] as $castType => $keyValues)
          {
          foreach($keyValues as $key => $fn2apply)
            {
              $dummy[$key] = null;
              if( isset($simpleXMLItems[$idx],$key) )
              {
                $dummy[$key] = $simpleXMLItems[$idx][$key];
                  settype($dummy[$key],$castType);
                  if(!is_null($fn2apply))
                  {
                    $dummy[$key] = $fn2apply($dummy[$key]);
                  }   
                }
            }
          } 
      }
      $items[$items_counter++] = $dummy;
      }
    }

  return $items;
}