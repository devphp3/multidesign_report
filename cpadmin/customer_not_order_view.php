<h1>Member Report</h1>
<div class="row">
    <div class="col-md-2">
        <label for="days">Days:</label>
        <input type="number" id="days" class="form-control" value="30">
    </div>
    <div class="col-md-2">
        <label for="website">Select Website:</label>
        <select id="website" class="form-control">
            <option value="">All Websites</option>
            <?php
            $sql = "SELECT id, website_name FROM digitizing_website";
            $result = $db->query($sql);
            if (is_array($result)) {
                foreach ($result as $row) {
                    echo "<option value='" . $row["id"] . "'>" . $row["website_name"] . "</option>";
                }
            }
            ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="businessType">Business/Non-Commercial:</label>
        <select id="businessType" class="form-control">
            <option value="">All</option>
            <option value="6">Business</option>
            <option value="5">Non-Commercial</option>
        </select>
    </div>
    <div class="col-md-2">
        <label for="activeStatus">Active/Inactive:</label>
        <select id="activeStatus" class="form-control">
            <option value="">All</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>

    <div class="col-md-2">
        <label>&nbsp;</label> <!-- Empty label for alignment -->
        <button id="applyFilter" class="btn btn-primary">Submit</button>
    </div>
</div>
<div class="table-responsive">
    <table id="reportTable" class="table table-bordered border table-hover">
        <thead>
            <tr>
                <th>Website</th>
                <th>Company ID</th>
                <th>Company Name</th>
                <th>Email</th>
                <th>Mobile</th>
            </tr>
        </thead>
        <tbody>
            <!-- Report data will be populated here dynamically using JavaScript. -->
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        var table = $('#reportTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            pageLength: 100, // Set the page length to 100 records per page
            ajax: {
                url: 'customer_not_order_process.php',
                type: 'POST',
                data: function(d) {
                    d.days = $('#days').val();
                    d.websiteId = $('#website').val();
                    d.businessType = $('#businessType').val();
                    d.activeStatus = $('#activeStatus').val();
                }
            },
            columns: [{
                    data: 'website_name',
                    orderable: false
                },
                {
                    data: 'sku',
                    orderable: false
                },
                {
                    data: 'company_name',
                    orderable: false
                },
                {
                    data: 'email',
                    orderable: false
                },
                {
                    data: 'mobile',
                    orderable: false
                },
            ]
        });

        $('#applyFilter').on('click', function() {
            var businessType = $('#businessType').val();
            var activeStatus = $('#activeStatus').val();

            table.ajax.reload(function(json) {
                json.businessType = businessType;
                json.activeStatus = activeStatus;
            });
        });
    });
</script>