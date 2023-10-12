<h1>Currency Wise Report</h1>
<div class="row d-flex align-items-end mb-24 pt-15">
    <div class="col-md-3">
        <label for="fromDate">Order completed From Date:</label>
        <input type="text" id="fromDate" class="form-control datepicker box-shadow">
    </div>
    <div class="col-md-3">
        <label for="toDate">Order completed To Date:</label>
        <input type="text" id="toDate" class="form-control datepicker box-shadow">
    </div>
    <div class="col-md-3">
        <label for="website">Select Website:</label>
        <select id="website" class="form-control select-size">
            <option value="">All Websites</option>
            <?php
            $sql = "SELECT id, website_name FROM digitizing_website";
            $result = $db->query($sql);
            if (is_array($result)) {
                foreach ($result as $row) {
                    // Generate options here
                    echo "<option value='" . $row["id"] . "'>" . $row["website_name"] . "</option>";
                }
            }
            ?>
        </select>
    </div>
    <div class="col-md-3">
        <button id="applyFilter" class="btn btn-primary">Submit</button>
    </div>
</div>
<div class="table-responsive">
    <table id="reportTable" class="table table-bordered border table-hover border">
        <thead>
            <tr>
                <th>Website Name</th>
                <th>Currency name</th>
                <th>No. of Orders Completed</th>
                <th>Total Amount (Without VAT)</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total:</th>
                <th id="totalOrdersCompleted">0</th>
                <th></th> <!-- Leave this column empty for alignment -->
            </tr>
        </tfoot>
    </table>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
    $(document).ready(function() {
        var currentDate = new Date();
        var toDate = new Date();
        toDate.setMonth(currentDate.getMonth() - 1);

        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy', // Set the date format to "dd-mm-yyyy"
            autoclose: true,
            endDate: currentDate,
        });

        // Set the default "From Date" to the current date and initialize the datepicker
        $('#toDate').val(moment(currentDate).format('DD-MM-YYYY')); // Use moment.js for formatting
        $('#toDate').datepicker('update');

        // Set the default "To Date" to 6 months from the current date and initialize the datepicker
        $('#fromDate').val(moment(toDate).format('DD-MM-YYYY')); // Use moment.js for formatting
        $('#fromDate').datepicker('update');

        var table = $('#reportTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false, // Disable search bar
            paging: false, // Disable pagination
            info: false,
            ajax: {
                url: 'currency_wise_view_process.php',
                type: 'POST',
                data: function(d) {
                    // Send default date values on initial load
                    d.fromDate = $('#fromDate').val();
                    d.toDate = $('#toDate').val();
                    d.websiteId = $('#website').val();
                }
            },
            columns: [{
                    data: 'website_name'
                },
                {
                    data: 'name'
                },
                {
                    data: 'record_count'
                },
                {
                    data: 'total_price'
                }
            ]
        });

        // Trigger the filter action on page load
        table.ajax.reload();
        table.on('draw.dt', function() {
            calculateTotal();
        });

        $('#applyFilter').on('click', function() {
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            var websiteId = $('#website').val();

            var fromDateObj = new Date(fromDate);
            var toDateObj = new Date(toDate);

            var yearDiff = toDateObj.getFullYear() - fromDateObj.getFullYear();
            var monthDiff = toDateObj.getMonth() - fromDateObj.getMonth();
            var dayDiff = toDateObj.getDate() - fromDateObj.getDate();
            console.log(yearDiff)
            if (yearDiff < 2) {
                table.ajax.url('currency_wise_view_process.php?fromDate=' + fromDate + '&toDate=' + toDate + '&websiteId=' + websiteId).load();
            } else {
                alert("Date difference must be less than 2 years.");
            }

        });

        function calculateTotal() {
            var totalOrdersCompleted = 0;
            table.rows().data().each(function(rowData) {
                totalOrdersCompleted += parseInt(rowData.record_count);
            });
            $('#totalOrdersCompleted').text(totalOrdersCompleted);
        }
        calculateTotal();
    });
</script>