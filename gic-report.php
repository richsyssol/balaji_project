
<?php 
    include 'session_check.php';

?>
  
  
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }
    th, td {
      border: 1px solid #000;
      padding: 8px;
      text-align: left;
    }
    th {
      background-color: #f2f2f2;
    }
    .center {
      text-align: center;
      font-weight: bold;
    }
    
    .footer {
      text-align: center;
    }
   
    .priority-table td {
      text-align: center;
    }
  </style>




  <div class="heading">

      <?php
                          
          $formatted_start_date = date("d/m/Y", strtotime($start_date));
          $formatted_end_date = date("d/m/Y", strtotime($end_date));
          echo "<h3 class='text-center'>GIC REPORT FOR $formatted_start_date TO $formatted_end_date </h3>"
          
      ?>

  </div>

  <p style="text-align:right;"><?php echo date('d-m-Y'); ?></p>


  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Policy Type</th>
        <th>Number of Policies</th>
        <th>Total Basic Premium</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($policy === 'Motor') { ?>
        <tr>
          <td>Motor</td>
          <td><?php echo $motor_count; ?></td>
          <td><?php echo $motor_amount; ?></td>
        </tr>
      <?php } elseif ($policy === 'NonMotor') { ?>
        <tr>
          <td>NonMotor</td>
          <td><?php echo $nonmotor_count; ?></td>
          <td><?php echo $nonmotor_amount; ?></td>
        </tr>
      <?php } else { // If All or empty ?>
        <tr>
          <td>Motor</td>
          <td><?php echo $motor_count; ?></td>
          <td><?php echo $motor_amount; ?></td>
        </tr>
        <tr>
          <td>NonMotor</td>
          <td><?php echo $nonmotor_count; ?></td>
          <td><?php echo $nonmotor_amount; ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>


    

    <table class="table table-bordered">
      <tr>
        <th>Check Points</th>
        <th>Deadline</th>
        <th>Check</th>
        <th>Dates</th>
      </tr>
      <tr><td>Update List / SB / Maturity</td><td>Day 1</td><td></td><td></td></tr>
      <tr><td>SMS</td><td>Upto 5</td><td></td><td></td></tr>
      <tr><td>Letters</td><td>Upto 5</td><td></td><td></td></tr>
      <tr><td>WhatsApp</td><td>Upto 5</td><td></td><td></td></tr>
      <tr><td>Calls</td><td>Upto 5</td><td></td><td></td></tr>
      <tr><td>Follow-Up SMS</td><td>Day 20</td><td></td><td></td></tr>
      <tr><td>Follow-Up Calls</td><td>Day 25</td><td></td><td></td></tr>
      <tr><td>Recheck</td><td>Day 27</td><td></td><td></td></tr>
      <tr><td>Final Check</td><td>Day 30</td><td></td><td></td></tr>
      <tr><td>Dispatch</td><td></td><td></td><td></td></tr>
    </table>

    

    <table class="table table-bordered">
      <tr>
        <th>Position At Month End</th>
        <th>Pending Renewals</th>
        <th>Total Basic Premium</th>
      </tr>
      <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
      </tr>
    </table>

    <table class="priority-table ">
      <tr>
        <th><input type="checkbox">Priority</th>
        <th><input type="checkbox">Speed</th>
        <th><input type="checkbox">Accuracy</th>
        <th><input type="checkbox">Deadline</th>
        <th><input type="checkbox">Delivery</th>
      </tr>
    </table>

    <div class="footer">
      <img src="asset/image/footer1.jpeg" alt="">
    </div>


  <!-- Table for print  -->
          <!-- class = summary-col -->
  <table class="table table-bordered my-5 ">
              <thead>
                  <tr>
                      <th scope="col" class="action-col">#</th>
                      <th scope="col">Reg No.</th>
                      <th scope="col">Date</th>
                      <th scope="col">Client Name</th>
                      <th scope="col">Policy Type</th>
                      <th scope="col">Premium</th>
                      <th scope="col">Company</th>
                      <th>_____Remark_____</th>
                  </tr>
              </thead>
                  <?php 
                      // Check if there are results to determine headers
                      if (isset($result) && $result->num_rows > 0) {
                          $first_row = $result->fetch_assoc();
                          // Reset the result pointer back to the start
                          $result->data_seek(0); 
                      }
                  ?>
              <tbody>
                  <?php 
                  if (isset($result) && $result->num_rows > 0) {
                      $srNo = 1;
                      while ($row = $result->fetch_assoc()) {
                          ?>
                          <tr>
                              <th scope="row" class="action-col"><?= $srNo++ ?></th>

                              <!-- Reg No. -->
                              <td><?= !empty($row['reg_num']) ? htmlspecialchars($row['reg_num']) : '-' ?></td>

                              <!-- Date -->
                              <td>
                                  <?php 
                                  if (!empty($row['policy_date'])) {
                                      $formatted_date = DateTime::createFromFormat('Y-m-d', $row['policy_date'])->format('d/m/Y');
                                      echo htmlspecialchars($formatted_date);
                                  } else {
                                      echo '-';
                                  }
                                  ?>
                              </td>

                              <!-- Client Name -->
                              <td>
                                  <?= !empty($row['client_name']) ? htmlspecialchars($row['client_name']) . '<br>' : '' ?>
                                  <?= !empty($row['contact']) ? htmlspecialchars($row['contact']) . '<br>' : '' ?>
                                  <?= !empty($row['address']) ? htmlspecialchars($row['address']) : '' ?>
                              </td>

                              <!-- Policy Type -->
                              <td><?= !empty($row['policy_type']) ? htmlspecialchars($row['policy_type']) : '-' ?></td>

                              <!-- Premium -->
                              <td>
                                  <?= !empty($row['mv_number']) ? htmlspecialchars($row['mv_number']) . '<br>' : '' ?>
                                  <?= !empty($row['vehicle']) ? htmlspecialchars($row['vehicle']) . '<br>' : '' ?>
                                  <?= !empty($row['sub_type']) ? htmlspecialchars($row['sub_type']) . '<br>' : '' ?>
                                  <?= !empty($row['nonmotor_type_select']) ? htmlspecialchars($row['nonmotor_type_select']) . '<br>' : '' ?>
                                  <?= !empty($row['nonmotor_subtype_select']) ? htmlspecialchars($row['nonmotor_subtype_select']) : '' ?>
                              </td>

                              <!-- Company -->
                              <td>
                                  <?= !empty($row['policy_company']) ? htmlspecialchars($row['policy_company']) . '<br>' : '' ?>
                                  <?= !empty($row['policy_number']) ? htmlspecialchars($row['policy_number']) . '<br>' : '' ?>
                                  <?php 
                                  if (!empty($row['end_date'])) {
                                      $formatted_date = DateTime::createFromFormat('Y-m-d', $row['end_date'])->format('d/m/Y');
                                      echo htmlspecialchars($formatted_date) . '<br>';
                                  }
                                  ?>
                                  <?= (!empty($row['amount']) && $row['amount'] != 0) ? htmlspecialchars($row['amount']) : '' ?>
                              </td>
                              <td class="summary-col"></td> <!-- Blank Summary column for print -->
                          </tr>
                          <?php
                      }
                  } else {
                      echo "<tr><td colspan='7' class='text-center'>No records found.</td></tr>";
                  }
                  ?>
              </tbody>
  </table>

