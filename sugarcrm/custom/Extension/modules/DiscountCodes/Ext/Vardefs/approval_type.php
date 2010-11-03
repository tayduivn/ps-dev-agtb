<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 4
 * Shows the approval_type_c dropdown if the code_type dropdown is set to Approval Code
 */


$dictionary['DiscountCodes']['fields']['approval_type_c']['dependency'] = 'equal($code_type, "approval_code")';

?>

