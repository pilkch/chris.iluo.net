<?PHP
	function embedStyleMainPage($imagesDirectory)
	{
?>
    <style type="text/css">
      body {
        margin:0px;
        padding:0px;
        font: 20px verdana, arial, helvetica, sans-serif;
        margin:0px 0px 0px 0px;
        color:#000000;
        background-color:#FFFFFF;
        text-align: left;
      }

      #peach {
        text-align: left;
        padding-left: 30px;
        margin-top: 0px;
        background-color: #f90;
        padding-top: 0px;
        height: 345px;
        padding-bottom: 0px;
        background: #0099ff url(<?PHP echo $imagesDirectory; ?>/main.png) no-repeat top left;
      }
      h1 {
        display: none;
      }

      h2, #conservatory {
        font: 10px Verdana, Arial, sans-serif;
        color: #666;
      }

      #delicti {
        margin: 20px;
      }

      #conservatory {
        margin-left: 20px;
        margin-top: 0px;
        text-align: left;
        padding-top: 5px;
        border-top: 5px solid #eee;
        padding-left: 20px;
      }


      ul {
        list-style-type: none;
        font-size: 18px;
      }

      li {
        padding: 3px;
      }

      li a {
        text-decoration: none;
        color: #666;
        border-bottom: 1px solid #eee;
      }

      li a:visited {
        color: #333;
        border-bottom: 1px solid #ddd;
      }

      li a:hover {
        color: #000;
        border-bottom: 1px solid #666;
      }
    </style>
<?PHP
  }

	function embedStyle($width=40, $height=100)
	{
  	$lf=1;

		$lh=154;


		$mainx=136;
		$mainy=88;
		$menux=6;
		$menuy=88;
		$menuwidth="18%";
		$mainwidth="82%"; //$width-$mainx+$menux;
?>
    <style type="text/css">
      body, main
			{
        background: #D3E0EA;
				color: #FFFFFF;
				font-size: smaller;
				font-family: "Verdana", "Arial";
      }
      
      a 
			{
      	color: #0000ff;
      }
      a:visited 
			{
      	color: #0000ff;
      }
      
      img 
			{
      	border: 0;
      }

			p 
			{
				margin-left: 1em;
				margin-right: 1em;
				margin-bottom: 0;
			}

      code 
			{
				font-family: "Courier", "Courier New", "Monospaced", "Serif";
      }

      
      .good
			{
        background: #99FF99;
      }
      
      .bad
			{
        background: #FF6666;
      }

			.err {background: #ffffff;
  			font-family: "Courier", "Courier New", "Monospaced", "Serif";
				font-size: 18px;
				color: #FF0000;
				border-style: solid; 
				border-width: 1px;
				border-top-width: 1px; 
				border-bottom-width: 1px;
				border-color: #000000;
					
				padding-top: 5px; 
				padding-bottom: 5px;
				padding-left: 7px;
				padding-right: 7px;
	
				clear: left;
			}
	
			.errentry 
			{
				background: #ffffff;
  			font-family: "Courier", "Courier New", "Monospaced", "Serif";
    		font-size: 18px;
    		color: #FF0000;
    		border-style: solid; 
  	    border-width: 1px;
  	    border-top-width: 1px; 
  	    border-bottom-width: 1px; 
  	    border-color: #000000;
  	     
  	    padding-top: 5px; 
  	    padding-bottom: 5px;
  	    padding-left: 7px;
  	    padding-right: 7px;
      
        clear: left;
			}





		..banner
			{
				background: #FFFFFF;
				color: #000000;
				border: 1px solid #000000;
				margin: 1em;
			}
	
			div.bannerimg{ 
				background: no-repeat;
				border: none;
				display: block !important;
			}
	
	
	
	
			.box 
			{
				background: #FFFFFF;
				color: #000000;
				border: 1px solid #000000;
				margin: 1em;
			}
	
			.box h1 a
			{
				color: #ffffff;
				background: #000000;
			}

	
	
	
			.menu
			{
				background-color: #ffffff; 
      	color: #000000;
				text-align: center;
      }

			div.menudiv{
				position: relative;
			}
	
			div.menuimg{ 
				background: repeat-x;
				border: none;
				display: block !important;
			}

			.menutitle
			{
				background: #000000;
				color: #ffffff;
				background-repeat: repeat-x;
				/*font-weight: bold;*/
				text-align: center;
			}



			#menu_header
			{
				width: <?PHP echo $menuwidth; ?>;
			}
			#menu_header .box .menuimg h1, .box h2, .box h3
			{
				font-size: 1em;
				color: #ffffff;
				padding: 3px;
				margin: 0;
			}
			#menu_header .box .menuimg h1 
			{
				text-align: center;
			}
			#menu_header ul
			{
				list-style: none;
				padding: 0;
				margin: 0.5em;
			}
			
			#menu_footer
			{
				width: <?PHP echo $menuwidth; ?>;
			}
			#menu_footer .box .menuimg h1, .box h2, .box h3
			{
				font-size: 1em;
				color: #ffffff;
				padding: 3px;
				margin: 0;
			}
			#menu_footer .box .menuimg h1 
			{
				text-align: center;
			}
			#menu_footer ul
			{
				list-style: none;
				padding: 0;
				margin: 0.5em;
			}
	
	
	
	
			#main
			{
				width: <?PHP echo $mainwidth; ?>;
			}
			
			#main .box .mainimg h1, .box h2, .box h3
			{
				font-size: 1em;
				color: #ffffff;
				margin: 0;
				padding: 3px;
			}
			#main .box .mainimg h1 
			{
				text-align: center;
			}
			



			#main .box h2, .box h3, .box h4, .box h5, .box h6 
			{
				color: #0000ff;
				background: none;
				font-weight: normal;
				margin: 0;
				padding-top: .5em;
				padding-bottom: .170em;
			}

			#main, .box h4, .box h5, .box h6 
			{
				border-bottom: none;
				/*font-weight: bold;*/
			}

			#main .box h2 {	font-size: 188%; }
			#main .box h3 {	font-size: 150%; }
			#main .box h4 { font-size: 132%; }
			#main .box h5 { font-size: 116%; }
			#main .box h6 { font-size: 100%; }

	
						
			#main p 
			{
				list-style: none;
				padding: 0;
				margin: 0.5em;
				
				color: black;
			}
			#main ul, #skipbar ul 
			{
				list-style: none;
			}
	
			#main .box h2 strong
			{
				color: #0000ff;
				font-weight: bold;
			}




			.comment
			{
				color: green;
			}

			.preproc
			{
				color: blue;
			}
			
			.keyword, .type
			{
				color: blue;
			}

			.number
			{
				color: red;
			}

			.string
			{
				color: purple;
			}
		</style>
<?
	}
?>
