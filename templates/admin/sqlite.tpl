<?php foreach ($_['database'] as $data): ?>
 <h2><?php echo $data['name']; ?></h2>
 <table class="database">
  <tr>
   <?php foreach ($data['columns'] as $column): ?>
    <td><strong><?php echo $column; ?></strong></td>
   <?php endforeach; ?>
  </tr>

  <?php foreach ($data['rows'] as $row): ?>
   <tr>
    <?php foreach ($row as $item): ?>
	   <td><?php echo (strlen($item) > 100 ? substr($item, 0, 100) . '...' : $item); ?></td>
    <?php endforeach; ?>
   </tr>
  <?php endforeach; ?>
 </table>
<?php endforeach; ?>