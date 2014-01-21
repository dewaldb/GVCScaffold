<div class="content-container">
    <?php print Render::pageTitle($GLOBALS["title"]); // use the render class to add proper css to the page title which is loaded from a template ?>
    <p><a href="<?php print $generic_name; ?>/add">Add a new <?php print Render::toTitleCase($generic_name); ?></a></p>
    <table id="generic-listing" class="table table-rounded table-striped table-bordered">
        <thead>
            <tr>
                <?php
                $count = 0;
                $max = count($fields);
                foreach($fields as $field=>$info) {
                    $count++;
                    if($count < 6 || $count == $max) {
                        print "<th>".Render::toTitleCase($field)."</th>";
                    } 
                }
                ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sortColumn = 0;
            foreach($generics as $generic) { 
                print "<tr>";
                $count = 0;
                $max = count($fields);
                foreach($fields as $field=>$info) { 
                    if($field=="createDate") {
                        $sortColumn = $count;
                    }
                    $count++;
                    if($count < 6 || $count == $max) {
                        print "<td>{$generic[$field]}</td>";
                    }
                }
                print "<td>";
                print "<a href='{$generic_name}/{$generic["id"]}/edit'>Edit</a> ";
                print "<a href='{$generic_name}/{$generic["id"]}/del'>Delete</a>";
                print "</td>";
                print "</tr>";
            }
            ?>
        </tbody>
    </table>
    <script>
        $(document).ready(function() {
            $('#generic-listing').dataTable( {
                "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
                "sPaginationType": "bootstrap",
                "aaSorting": [[ <?php print $sortColumn; ?>, "desc" ]]
            } );
        } );
    </script>
</div>