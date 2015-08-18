<?php

/*###################################################################################################
	## 	File Input Output Library 
	## 	Made 10/11/2014 by   Etio@
	##  Last Update on 19/10/2014
	## 	Tested by ...
	##  v1.0
#####################################################################################################*/



###################################################################################################
#										FILE_IO	    	     			  						  #
###################################################################################################

class  File_IO
{
	private $filename;

		public function __construct( $_filename="" )
		{
			$this->filename =  $_filename;
		}

		/*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/############`#####################################

		public static function saveData( $destination , $message, $glue=null )
		{
			try
			{
				$glue =is_null( $glue ) && gettype($message)=="array"? "	|	" : $glue;

				if(self::check_file_existence_and_create( $destination ))
				{
					$fh = fopen($destination, 'w') or die("can't open file : ".$destination); 
					if(gettype($message) == "array")
					{
						$message = implode($glue, $message);
					} 
            		fwrite($fh, $message);
            		fclose($fh);
            		//return true;
				}


			}catch(Exception $e) { var_dump( $e ) ; }

		} 

		/*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################
		public static function appendData( $destination , $message, $glue=null )
		{
			try
			{
				$glue =is_null( $glue ) && gettype($message)=="array"? "	|	" : $glue;

				if(self::check_file_existence_and_create( $destination ))
				{
					$fh = fopen($destination, 'a') or die("can't open file : ".$destination); 
					if(gettype($message) == "array")
					{
						$message = @implode($glue, $message);
					} 
            		fwrite($fh, PHP_EOL.$message.PHP_EOL);
            		fclose($fh);
				}
		
			}catch(Exception $e) { var_dump( $e ) ; }

		} 

		/*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################
    	public static function getData( $destination,$type ="string", $delimiter=null, $limit_per_page=300,$page=0 )
		{
			try
			{
				
			 	$i=0;
			 	$seekp=0;

				if( file_exists( $destination ) )
				{
					$fp = fopen($destination, 'r') or die("can't open file : ".$destination); 
					
					if( trim(strtolower($type)) == "array" )
					{
						$res = array();
						
						while( $line = fgets($fp) )
						{
							$range_condi = ( $page * $limit_per_page ) <= $i && $i <= ( ($page * $limit_per_page)+ $limit_per_page);
							$seekp += strlen($line);

							if( $range_condi )
							$res[] =  trim( $line );
							$i++;
						}
					}
					elseif ( trim(strtolower($type)) == "array2d" ) {
						
						$delimiter = is_null( $delimiter ) ? "|":$delimiter;
						$res = array();
						while( $line = fgets($fp) )
						{
							$seekp += strlen($line);
							$range_condi = ( $page * $limit_per_page ) <= $i && $i <= ( ($page * $limit_per_page)+ $limit_per_page);

							if( $range_condi )
							$res[] =  explode($delimiter, trim($line) ) ;
							$i++;
						}
					}
					else
					{
						$res = "";
						while( $line = fgets($fp) )
						{
							$seekp += strlen($line);
							$range_condi = ( $page * $limit_per_page ) <= $i && $i <= ( ($page * $limit_per_page)+ $limit_per_page);

							if( $range_condi )
							$res .= "\n".trim( $line );
							$i++;
						}
					}

					fclose($fp);
					return $res;

				}else { echo "file does not exist";}

			}catch(Exception $e) { var_dump( $e ) ; }

		} 


		/*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################
   	      public static function select( $destination, $group_delimiters, $limit_per_page=300,$page=0 )
   	      {
   	      	try
   	      	{
   	      		$i=0;
			 	$seekp=0;

				if( file_exists( $destination ) )
				{
					$fp = fopen($destination, 'r') or die("can't open file : ".$destination); 
					
						$res = array();
						while( $line = fgets($fp) )
						{
							$seekp += strlen($line);
							$range_condi = ( $page * $limit_per_page ) <= $i && $i <= ( ($page * $limit_per_page)+ $limit_per_page);

							if( $range_condi )
							{
								$line= trim( $line );
								$collection = array();
								foreach ($group_delimiters as $delimiters) {

									if( isset($delimiters[0]) && isset($delimiters[1]) )
									{
										$delimiters[0] = preg_quote($delimiters[0]);
										$delimiters[1] = preg_quote($delimiters[1]);

										//echo $delimiters[0];

										$tmp = isset( preg_split("#".$delimiters[0]."#", $line)[1]) ?  preg_split("#".$delimiters[0]."#", $line)[1] :null;
										$tmp = isset( preg_split("#".$delimiters[1]."#",$tmp)[0]) ?   preg_split("#".$delimiters[1]."#",$tmp)[0] : null;

										if($tmp!=null)
										{
											$collection[] = $tmp;
										}
									}
								}
								

								if(count($collection) != 0)
								{
									$res[]= implode( $collection , "	|	") ;
								}

							}
							
							$i++;
						}					

					fclose($fp);
					return $res;

				}else { echo "file does not exist";}

   	      	}catch(Exception $e){ var_dump( $e);}
   	      	
   	      }


		/*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################
   	      public static function update( $filename, $group_delimiters, $limit_per_page=300,$page=0 )
   	      {
   	      	try
   	      	{

   	      	}catch( Exception $e){ var_dump( $e );}
   	      }

   	    /*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################
   	      public static function create( $filename )
   	      {
   	      	try
			{
				if(self::check_file_existence_and_create($filename))
				{
					$fh = fopen($filename, 'w') or die("can't open file : ".$filename); 
            		fwrite($fh,"");
            		fclose($fh);
            		return true;
				}

			} catch(Exception $e) { var_dump( $e ) ; }

			return false;
   	      }


   	    /*#################################################
	  		@Descri :  
	  		@Params : 
	  		@Return : 
		*/#################################################
		public static function join($filenames , $joint_filename)
		{
			$buffers=array();
			try
			{
				if(gettype($filenames) !='array' )
					return false;
				foreach($filenames as $filename) {

					if(!$buffers[] = "\n".trim(self::getData($filename)) )
						return false;
				}
				$content = trim(implode("\n", $buffers));
				if(!self::saveData($joint_filename, $content) )
					return false;
				 
				

			}catch( Exception $e ) { var_dump( $e ); }

			return true;
		}

		/*#################################################
	  		@Descri :  
	  		@Params : 
	  		@Return : 
		*/#################################################
		public static function find($filename , $needle)
		{
			try
			{
				
				$needle = trim(preg_quote($needle));

				if( file_exists( $filename ) )
				{
					$fp = fopen($filename, 'r') or die("can't open file : ".$destination); 
					
						$res = 0;
						while( $line = fgets($fp) )
						{
							if( preg_match("#".$needle."#i", $line))
							{
								$res ++;
								
							}
						}
					

					fclose($fp);
					return $res;

				}else { echo "file does not exist";}


				
			}catch( Exception $e ) { var_dump( $e ); }
		}

		/*#################################################
	  		@Descri :  
	  		@Params : 
	  		@Return : 
		*/#################################################
		public static function find_n_replace($filename , $needle, $subtitute)
		{
			try
			{
				if(!file_exists($filename) )
					return false;

				if( !$data = self::getData($filename))
					return false;
					

				$needle = preg_quote($needle);
				$data = preg_replace("#".$needle."#i", $subtitute, $data);

				if( !self::saveData($filename , trim($data)) )
					exit("can't save file");

				
			}catch( Exception $e ) { var_dump( $e ); }

			return true;
		}




		/*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################
	 	public static function check_file_existence_and_create( $destination )
		{
			try
			{
				if( file_exists($destination) )
				{	
					return true;
				}
				else
				{
					$path_element = explode("/", $destination);
					$res="";
					for( $i =0 ; $i < count($path_element) ; $i++ )
					{	
						if( $i == count($path_element) -1 )
							break;

						$res .= $path_element[$i].'/';
						if(!is_dir($res))
						{
							self::createDirectory($res);
						}
					}
					$fp = fopen($destination, 'w') or die("can't open file : ".$destination) ;
					fwrite($fp , "");
					fclose($fp);
					return true;

				}
			}catch(Exception $e) { var_dump( $e ) ;}
			
			return false;
		}


		/*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################
   	      public static function emptyFile( $filename )
   	      {
   	      	try
			{
				if(file_exists($filename))
				{
					$fh = fopen($filename, 'w') or die("can't open file : ".$destination); 
            		fwrite($fh,"");
            		fclose($fh);
            		return true;
				}

			}catch(Exception $e) { var_dump( $e ) ; }

			return false;
   	      }



   	     /*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################
   	      public static function dropFile( $filename )
   	      {
   	      	try
   	      	{
   	      		if( file_exists( $filename ) )
   	      		{
   	      			chmod( $filename, 0777); 
   	      			return unlink(realpath( $filename));
   	      		}else{ return false; }

   	      	}catch( Exception $e ){ var_dump( $e );}
				
   	      	return false;
   	      }



   	     /*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################
   	      public static function renameFile( $filename, $newfilename )
   	      {
   	      		try
   	      		{
   	      			if( file_exists( $filename) )
   	      			{
   	      				rename($filename,  $newfilename);
   	      				return true;
   	      			}
   	      			else
   	      				return false;
   	      			
   	      		} catch ( Exception $e){ var_dump( $e ); }
   	      }


    	/*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################	      
		public static function copyFile($old_path , $new_dir , $_filename ,$can_overwrite = false )
		{
			$new_path = $new_dir.'/'.$_filename;
			try
			{	if( file_exists( $old_path ) )
				{
					if(self::check_file_existence_and_create($new_dir))
					{
						if( file_exists( $new_path ) && $can_overwrite == false )
						{
							return false;
						}
						else
						{
							copy($old_path , $new_path) or die('unable  to copy $old to $new');
							return true;
						}
					}

				}else{ echo "file ".$old_path." does not exist" ;}

			}catch( Exception $e){ var_dump( $e ); }
			
		}

		 /*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################	      
		public static function moveFile($old_path , $new_dir , $_filename, $can_overwrite = false)
		{
			try
			{
				if( self::copyFile( $old_path , $new_dir , $_filename,$can_overwrite ) )
				{
					if(file_exists($new_dir."/".$_filename) && file_exists($old_path))
					{
						return self::dropFile($old_path) ;
					}
				}else{ echo "can't move file"; }
			}catch( Exception $e){ var_dump( $e ); }
			
		}



  		/*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################

		public static function createDirectory( $dir )
		{
			try
			{
				if(is_dir($dir))
					exit('Directory already exist');
				if (!mkdir($dir, 0777, true)) {
 			 	  die('Failed to create folders...');
				}
			} catch( Exception $e ) { var_dump( $e );}

			return true;

		}



  		/*#################################################
    	  @Descri :  
    	  @Params : 
   	      @Return : 
  		*/#################################################
		public static function removeDirectory( $dir )  #need pemission
		{
			try
			{
				
				if (is_dir($dir)) 
				{
			     $objects = scandir($dir);

			     foreach ($objects as $object) {
			       	if ($object != "." && $object != "..") {
			         if (filetype($dir."/".$object) == "dir") 
			         {
			         	chmod( $dir."/".$object, 0775); 
			         	self::removeDirectory($dir."/".$object);
			         }
			         	
			         else unlink($dir."/".$object);
			       }
			     }
			     reset($objects);
			     chmod( $dir , 0775); 

			     return rmdir($dir);

			      
		   		}else{  return false; }
				
			} catch( Exception $e){ var_dump( $e );}
		
			
			return false;
		}


		/*#################################################
	  		@Descri :  
	  		@Params : 
	  		@Return : 
		*/#################################################
		public static function scanFolder($path)
		{
			try
			{
				$dir= $path;
				$res = array();
				$objects = scandir($dir,1);
				   foreach ($objects as $object) {
			       	if ($object != "." && $object != "..") {
			        
			        	$res[] = $object;
			       }
			     }
				
				#echo  json_encode($files1) ;
				return json_encode($res);
				
			}catch( Exception $e ) { var_dump( $e ); }
		}

		/*#################################################
	  		@Descri :  
	  		@Params : 
	  		@Return : 
		*/#################################################
	  	public static function uploadFile($file, $server_dir)
	  	{
	  		try
	  		{
	  		if(count($file)) 
	  		{
			  if($file["file"]['name'] !='')
			  {
				$alloweExts =  array("gif", "jpeg","jpg", "png");
				$temp = explode(".", $file["file"]['name']);
				$extension = end($temp);

				if(true)
				{
					#echo "C:/xampp/htdocs/fusion/assets/img/gallery/".$file["file"]['name'] ;
					return move_uploaded_file($file['file']['tmp_name'], $server_dir.'/'.$file["file"]['name'] ) or die("\n file cannot be uploaded");	
				}
				else
				{
					echo " \n This file has been restricted";
					return false;
				}
			  }
			}
			  return false;
			}catch( Exception $e){var_dump(var_dump($e));}


	  	} 


	  	/*#################################################
	  		@Descri :  
	  		@Params : 
	  		@Return : 
		*/#################################################
	  	public static function getServerTime()
	  	{
	  		date_default_timezone_set('UTC');
			return (string)date('Y/m/d H:i:s');
	  	} 




		/*#################################################
	  		@Descri :  
	  		@Params : 
	  		@Return : 
		*/#################################################
		public static function get_characters_stats($path)
		{
			try
			{
				$dir= $path;
				$files1 = scandir($dir,1);
				echo  json_encode($files1) ;
				return json_encode($files1);
				
			}catch( Exception $e ) { var_dump( $e ); }
		}

	    /*#################################################
	  		@Descri :  
	  		@Params : 
	  		@Return : 
		*/#################################################
		public static function get_lines_stats($path)
		{
			try
			{
				$dir= $path;
				$files1 = scandir($dir,1);
				echo  json_encode($files1) ;
				return json_encode($files1);
				
			}catch( Exception $e ) { var_dump( $e ); }
		}

		/*#################################################
	  		@Descri :  
	  		@Params : 
	  		@Return : 
		*/#################################################
		public static function get_file_info($path)
		{
			try
			{
				$dir= $path;
				$files1 = scandir($dir,1);
				echo  json_encode($files1) ;
				return json_encode($files1);
				
			}catch( Exception $e ) { var_dump( $e ); }
		}


}

//$fio = new file_io();

 //var_dump($fio::removeDirectory("Dodo/lois/Dadi/Piss"));

//var_dump($fio::select("lol/pro.txt",array( array("~~", "~~"),array("@@","--"),array("[[","~~") ) ) );
//var_dump($fio::find("lol/pro.txt","star" ));
//var_dump($fio::join(array("lol/pro.txt","hi.txt","tato.txt"),"star.txt" ));
//var_dump($fio::saveData("lol/55",array("gddf","sddfsd","sffssdfs"),"	#	"));
//var_dump($fio::getServerTime());
//var_dump($fio::uploadFile(""));

# delete
# create
# appendData
# saveData
# getData
# copyFile
# moveFile
# check_file_existence_and_create
# emptyFile
# dropFile
# renameFile
# createDirectory
# scanFolder
# removeDirectory
# select
# find
# join
# find_replace

# ~ update 

###################################################################################################
#										END FILE_IO	    	        	  						  #
###################################################################################################


?>