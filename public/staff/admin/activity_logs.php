<?php 
require_once('../../../private/config.php'); 

require_password_reset();

if (!hasPermission('view_logs')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}

?>



<?php

$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if ($page < 1) {
	$page = 1;
	}
	
$totalPages = total_page_count($limit);

$search = $_GET['search'] ?? null;
$startDate = $_GET['start'] ?? null;
$endDate = $_GET['end'] ?? null;
$onlyAdmin = isset($_GET['admin']);

$all_logs = display_logs($search, $startDate, $endDate, $onlyAdmin, $limit, $offset);
 
$page_title = 'Log of Activities';

$specificCss = '/assets/css/add_staff.css';

include(SHARED_PATH . '/header.php'); ?>

<div id="content">
  
  <?php include(SHARED_PATH . '/navigation.php'); ?>
  
  <main class="main-content">
    
  <i class="bi bi-arrow-left"></i> Back
</a>
    
  <div class="top">
    <p class="top-head">Activity Logs </p> 
    <p class="top-description">list of changes and important actions done</p>
  </div>
    
 

    
  <div class="tabs" role="tablist">
  <button role="tab" class="tab-btn active" aria-selected="true" aria-controls="logs" data-tab="logs">
    Logs
  </button>

</div>

<div id="logs" class="tab-content active" role="tabpanel" aria-labelledby="logs-tab">
    <form method="GET" style="display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
	
	<div>
	
	<span style="color: #666666; height: 15px; border: 1px solid #666666; border-left: 3px solid green; padding: 4px 2px;">
	
	<i class="bi bi-search"></i>
	
		<input type="text" name="search" placeholder="Search performer/action..." 
		style="color: #666666; outline: none; border: none; margin: 15px 0; width: 200px; height: 15px;" 
		value="<?php echo v_wrap($search ?? ''); ?>">
		
		</span></div>
	
	   <label>
		 Start Date <input type="date" name="start" value="<?php echo $startDate ?? ''; ?>"> 
	   </label>
	   
	   <label>
		 End Date <input type="date" name="end" value="<?php echo $endDate ?? ''; ?>"> 
	   </label>

		<label>
			<input type="checkbox" name="admin"<?php if($onlyAdmin) echo 'checked'; ?>> Admin only 
		</label>
		
	<div>
		<button id="filterBtn" type="submit">Filter</button></div>
	</form>
	
	<table class="staff-list">
  	  <tr>
        <th>Performing Staff</th>
		<th>Role of Performing Staff</th>
        <th>Action Taken</th>
		<th>Entity Performed On</th>
		<th>ID of Entity</th>
		<th>Date</th>
		
	  </tr>
	  <?php 
	  
      
      while($audit_logs = $all_logs->fetch_assoc()) { ?>
        
        <tr>
			<td><?php echo v_wrap($audit_logs['full_name'] ?? 'Deleted User'); ?></td>
			<td><?php echo v_wrap($audit_logs['role'] ?? 'N/A'); ?></td>
			<td><?php echo v_wrap($audit_logs['action']); ?></td>
			<td><?php echo v_wrap($audit_logs['entity_type']); ?></td>
			<td><?php echo v_wrap($audit_logs['entity_id']); ?></td>
			<td><?php echo v_wrap($audit_logs['created_at']); ?></td>
</tr>
		
		<?php } // close while statement ?>
	</table>	
</div>
<div style="margin-top:20px; text-align: center;">

<?php
$queryString = http_build_query([
  'search' => $search,
  'start' => $startDate,
  'end' => $endDate,
  'admin' => $onlyAdmin ? 1 : null
]);
?>

  
  <?php if ($page > 1) { ?>
  <a href="?page=<?php echo $page - 1; ?>&<?php echo $queryString; ?>">⬅ Prev</a>
<?php } ?>

  <span> Page <?php echo $page; ?> </span>
  
	<?php if ($page < $totalPages) { ?>
		<a href="?page=<?php echo $page + 1; ?>&<?php echo $queryString; ?>">Next ➡</a>
	<?php } ?>
  

</div>

</main>

</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
