<?php
include 'session.php';
include 'commonhead.php';
//include 'dbcontroller.php';

$month_year = $_POST['NoIconDemo'];
$sewadar_name1 = $_POST['sewadar_name1'];
$sewadar_name2 = $_POST['sewadar_name2'];
$sewadar_name3 = $_POST['sewadar_name3'];
$sewadar_name4 = $_POST['sewadar_name4'];

$n = 0;
if($month_year !=''){
	$month_years = explode("/", $month_year);
	$month = $month_years[0];
	$year = $month_years[1];
}
else{
echo "<p>Insertion Failed <br/> Some Fields are Blank....!!</p>";
}

function get_table()
{
	$db_handle = new DBController();

	$slip_count = 0;
	global $year, $month, $n, $sewadar_name1, $sewadar_name2, $sewadar_name3, $sewadar_name4;
	$sewadar_name = array();
	for($i=1; $i <= 4; $i++) {
	    if(isset(${'sewadar_name' . $i}) && !empty(${'sewadar_name' . $i})){
	    	array_push($sewadar_name, ${'sewadar_name' . $i});
	    	$slip_count++;
	    }
	}
	$sewadar_type = array();
	$slip_type = array();
	$sewa_type = array();

	for ($i=0; $i < $slip_count; $i++) { 
		
		$sewadar_list = $db_handle->runQuery("SELECT * FROM sewadars WHERE sewadar_name = '".$sewadar_name[$i]."'");
		foreach ($sewadar_list as $sewadar) {
			$sewadar_type[$i] = $sewadar["type"];
		}
		if (strcmp($sewadar_type[$i], "SK") == 0) {
			$slip_type[$i] = "SATSANG KARTA SEWA SLIP";
			$sewa_type[$i] = "PATHI";
		}
		else if (strcmp($sewadar_type[$i], "SR") == 0) {
			$slip_type[$i] = "SATSANG READER SEWA SLIP";
			$sewa_type[$i] = "PATHI & SATSANG";
		}
		else if (strcmp($sewadar_type[$i], "Pathi") == 0) {
			$slip_type[$i] = "PATHI SEWA SLIP";
			$sewa_type[$i] = "SEWA";
		}
	}

	$monthObj   = DateTime::createFromFormat('!m', $month);
	$monthName = $monthObj->format('M');

	$year_short = substr($year, 2, 4);

	$master_table = "master"."-".$monthName."-".$year_short;

	$table_str = "";

	$weekday_list = array("SUNDAY", "WEDNESDAY", "THURSDAY");
	$weekdays = array();

	foreach ($weekday_list as $w) {
		foreach (getWeekdays($year, $month, $w) as $weekday) {
			$weekdays[$weekday->format("d")] = substr($w, 0, 3);
		}
	}
	ksort($weekdays);

	$sr_names = array();

	if (!empty($slip_count)) {
		for ($i=0; $i < $slip_count; $i++) {

			if ($n%4 == 0) {
				$div = '<div id="col0">
								<div id="rect0">';
				$div_end = '</div>';
			}
			elseif ($n%4 == 1) {
				$div = '<div id="rect2">';
				$div_end = '</div></div>';
			}

			elseif ($n%4 == 2) {
				$div = '<div id="col1">
								<div id="rect1">';
				$div_end = '</div>';
			}

			elseif ($n%4 == 3) {
				$div = '<div id="rect3">';
				$div_end = '</div></div>';
			}

			$table_str .= $div;
			
			$table_str .= '<table border = 1 width= "30%" style = "margin = 0px;padding = 0px;height=100%">';

			for ($row = 1; $row <= 3; $row++) 
			{

				$table_str .= '<tr align="center">';
				for ($col=1; $col <= 4; $col++) {
						
					if ($row == 1) 
					{
						if ($col ==1) {
							$table_str .= '<th colspan = 3 width = "60%" style="text-align: center; font-family:arial;">'.$slip_type[$i].'</th>';
							$col++;
						}
						elseif ($col ==3) {
							$dateObj   = DateTime::createFromFormat('!m', $month);
							$monthName = $dateObj->format('M');

							$year_short = substr($year, 2, 4);
							
							$table_str .= '<th width = "40%" style="text-align: center; font-family:arial;">'.$monthName."-".$year_short.'</th>';
							$col++;
						}
						
					}

					elseif ($row == 2) 
					{
						$table_str .= '<th colspan = 4 style="text-align: center; font-family:arial;">'."Name: ".$sewadar_name[$i].'</th>';
						break;
					}

					elseif ($row == 3) 
					{
						if ($col ==1) {
							$table_str .= '<th width = "10%" style="text-align: center; font-family:arial;">'."DT.".'</th>';
						}
						elseif ($col ==2) {
							$table_str .= '<th width = "20%" style="text-align: center; font-family:arial;">'."DAY".'</th>';
						}
						elseif ($col ==3) {
							$table_str .= '<th width = "35%" style="text-align: center; font-family:arial;">'."SATSANG PLACE".'</th>';
						}
						elseif ($col ==4) {
							$table_str .= '<th width = "35%" style="text-align: center; font-family:arial;">'.$sewa_type[$i].'</th>';
						}
					}
				}
				
				$table_str .= '</tr>';
			}
			if (strcmp($sewadar_type[$i], "Pathi") == 0) {
				$sr_array = $db_handle->runQuery("SELECT * FROM `".$master_table."` WHERE `pathi` = 'PA: ".$sewadar_name[$i]."' OR `ground` = 'SS: ".$sewadar_name[$i]."'");
			}
			else{
				$sr_array = $db_handle->runQuery("SELECT * FROM `".$master_table."` WHERE sk_sr = '".$sewadar_type[$i].": ".$sewadar_name[$i]."'");
			}
			foreach ($weekdays as $date => $day) {
				
				$table_str .= '<tr>';
				
				$table_str .= '<td width = "10%" align="center" style="font-family:arial;">'.$date.'</td>';

				$table_str .= '<td width = "20%" align="center" style="font-family:arial;">'.$day.'</td>';
				
				$centre_name = "";
				$sk_sr = "";

				foreach ($sr_array as $sr_name) {

					$d = date('d', strtotime($sr_name['date']));

					if (strcmp($d, $date) == 0) {

						$centre_name = $sr_name['centre_name'];
						if (strcmp($sewadar_type[$i], "Pathi") == 0) {
							if (strcmp($sewadar_name[$i], explodeCheck($sr_name['pathi'], ":")) == 0 && $sr_name['row'] != 0) {
								$sk_sr = nl2br("PATH SEWA (".explodeCheck($sr_name['sk_sr'], ":").")\n(GR SEWA-".explodeCheck($sr_name['ground'], ":").")");
							}
							else {
								if (strcmp("CD", $sr_name['sk_sr'])==0) {
									$sk_sr = "GR SEWA (CD)";
								} else {
									$sk_sr = nl2br("GR SEWA (".explodeCheck($sr_name['sk_sr'], ":")."-".explodeCheck($sr_name['pathi'], ":").")");
								}
							}
						}
						else{
							$sk_sr = explodeCheck($sr_name['pathi'], ":");
						}	
					}
				}
				
				$date_full = $date.'.'.$month.'.'.$year;

				$table_str .= '<td width = "35%">'.$centre_name.'</td>';
				
				$table_str .= '<td width = "35%">'.$sk_sr.'</td>';

				$table_str .= '</tr>';
			}

			$table_str .= '<tr rowspan = 4>';

			$table_str .= '<th colspan = 4 style="text-align: right; height = 70px; padding-top: 15%; font-family:arial;">'."SECRETARY, AHMEDABAD".'</td>';

			$table_str .= '</tr>';

			$table_str .= '</table>';

			$table_str .= $div_end;

			$n++;
		}
	}
	return $table_str;
}

function getWeekdays($y, $m, $w)
{
    return new DatePeriod(
        new DateTime("first ".$w." of $y-$m"),
        DateInterval::createFromDateString('next '.$w),
        new DateTime("next month $y-$m-01")
    );
}
function explodeCheck($str, $del)
{	
	if (strpos($str, $del) != false) {
		return TRIM(explode($del, $str)[1]);
	} else {
		return "";
	}
}
?>

<div class="row">
	<div class="col-xs-12">
		<!-- PAGE CONTENT BEGINS -->
						
		<?php echo get_table();		
				
		?>
		<!-- PAGE CONTENT ENDS -->
	</div><!-- /.col -->
</div><!-- /.row -->
			</div><!-- /.page-content -->


			
		</div>
	</div><!-- /.main-content -->

			<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
				<i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
			</a>
		</div><!-- /.main-container -->

		<!-- basic scripts -->

		<!--[if !IE]> -->
		<script src="assets/js/jquery-2.1.4.min.js"></script>

		<!-- <![endif]-->

		<!--[if IE]>
<script src="assets/js/jquery-1.11.3.min.js"></script>
<![endif]-->
		<script type="text/javascript">
			if('ontouchstart' in document.documentElement) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		<script src="assets/js/bootstrap.min.js"></script>

		<!-- page specific plugin scripts -->
		<script>
		function showEdit(editableValue) {
			$(editableValue).css("background","#FFF");
		} 
		
		function saveToDatabase(editableObj, editableValue, master, sk_sr, date, weekday) {
			console.log('editObj='+editableObj+'&editval='+editableValue.innerHTML+'&master='+master+'&sk_sr='+sk_sr+'&date='+date+'&day='+weekday);

			$(editableObj).css("background","#FFF url(assets/images/loaderIcon.gif) no-repeat right");
			$.ajax({
				url: "sk_saveedit.php",
				type: "POST",
				data:'editObj='+editableObj+'&editVal='+editableValue.innerHTML+'&master='+master+'&sk_sr='+sk_sr+'&date='+date+'&day='+weekday,
				success: function(data){
					$(editableObj).css("background","#FDFDFD");
				}        
		   });
		}
		</script>
		<!-- ace scripts -->
		<script src="assets/js/ace-elements.min.js"></script>
		<script src="assets/js/ace.min.js"></script>

		<!-- inline scripts related to this page -->
		<script type="text/javascript">
			jQuery(function($) {
			 var $sidebar = $('.sidebar').eq(0);
			 if( !$sidebar.hasClass('h-sidebar') ) return;
			
			 $(document).on('settings.ace.top_menu' , function(ev, event_name, fixed) {
				if( event_name !== 'sidebar_fixed' ) return;
			
				var sidebar = $sidebar.get(0);
				var $window = $(window);
			
				//return if sidebar is not fixed or in mobile view mode
				var sidebar_vars = $sidebar.ace_sidebar('vars');
				if( !fixed || ( sidebar_vars['mobile_view'] || sidebar_vars['collapsible'] ) ) {
					$sidebar.removeClass('lower-highlight');
					//restore original, default marginTop
					sidebar.style.marginTop = '';
			
					$window.off('scroll.ace.top_menu')
					return;
				}
			
			
				 var done = false;
				 $window.on('scroll.ace.top_menu', function(e) {
			
					var scroll = $window.scrollTop();
					scroll = parseInt(scroll / 4);//move the menu up 1px for every 4px of document scrolling
					if (scroll > 17) scroll = 17;
			
			
					if (scroll > 16) {			
						if(!done) {
							$sidebar.addClass('lower-highlight');
							done = true;
						}
					}
					else {
						if(done) {
							$sidebar.removeClass('lower-highlight');
							done = false;
						}
					}
			
					sidebar.style['marginTop'] = (17-scroll)+'px';
				 }).triggerHandler('scroll.ace.top_menu');
			
			 }).triggerHandler('settings.ace.top_menu', ['sidebar_fixed' , $sidebar.hasClass('sidebar-fixed')]);
			
			 $(window).on('resize.ace.top_menu', function() {
				$(document).triggerHandler('settings.ace.top_menu', ['sidebar_fixed' , $sidebar.hasClass('sidebar-fixed')]);
			 });
			
			
			});
		</script>
	</body>

	<style type="text/css">
		body {
		width: 100%;
		height: 100%;
		padding: 0px;
		margin: 0px;
		}

		html {
		width: 100%;
		height: 100%;
		padding: 0px;
		margin: 0px;
		}

		table {
		width: 95%;
		height: 95%;
		padding: 0px;
		margin: 0px;
		}

		tr,td, th {
			border: 1px solid black;
			text-align: center;
			font-family:arial;
		}
		
		#col0 {
		float: left;
		width: 50%;
		height: 100%;
		padding: 0px;
		margin: 0px;
		}

		#col1 {
		float: right;
		width: 50%;
		height: 100%;
		padding: 0px;
		margin: 0px;
		}

		#rect0 {
		width: 100%;
		height: 50vh;
		padding: 0px;
		margin: 0px;
		}

		#rect1 {
		width: 100%;
		height: 50vh;
		padding: 0px;
		margin: 0px;
		}

		#rect2 {
		width: 100%;
		height: 50vh;
		padding: 0px;
		margin: 0px;
		}

		#rect3 {
		width: 100%;
		height: 50vh;
		padding: 0px;
		margin: 0px;
		}
	</style>
</html>
