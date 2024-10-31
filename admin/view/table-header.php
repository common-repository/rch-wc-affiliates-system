<?php

defined( 'ABSPATH' ) or exit;

$default_asc_columns = array( 'code', 'name' );

foreach ( $table_columns as $column_id => $column ) {
    ?>
    <th class="rchp-admin-table-sortable-column" data-column="<?php echo $column_id; ?>">
        <?php
            echo $column['label'] . '&nbsp;';

            if ( $GLOBALS['rchp_sort'] == $column_id ) {
                if ( $GLOBALS['rchp_sort_order'] == 'asc' ) {
                    echo '<i class="fas fa-sort-up rchp-sort"></i>';
                } else {
                    echo '<i class="fas fa-sort-down rchp-sort"></i>';
                }
            } else {
                if ( $column['default_sort'] == 'asc' ) {
                    echo '<i class="rchp-sort rchp-invisible fas fa-sort-down"></i>';
                } else {
                    echo '<i class="rchp-sort rchp-invisible fas fa-sort-up"></i>';
                }
            }
        ?>
    </th>
    <?php
}
