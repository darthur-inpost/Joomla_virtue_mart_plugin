<?php
defined('_JEXEC') or die('Restricted access');

AdminUIHelper::startAdminArea($this);
AdminUIHelper::imitateTabs('start','COM_VIRTUEMART_PARCELSTATUS_DETAILS');

// Get the plugins
//JPluginHelper::importPlugin('vmpayment');
//JPluginHelper::importPlugin('vmshopper');
//JPluginHelper::importPlugin('vmshipment');

?>

<script type="text/javascript" src="<?php echo inpostparcels_helper::getGeowidgetUrl(); ?>"></script>
<script type="text/javascript">
function user_function(value)
{
        var address = value.split(';');
        var openIndex = address[4];
        var sufix = '';

	if(openIndex == 'source_machine')
	{
		sufix = '_source';
        }

        //document.getElementById('town').value=address[1];
        //document.getElementById('street').value=address[2]+address[3];
        var box_machine_name   = document.getElementById('name').value;
        var box_machine_town   = document.value=address[1];
        var box_machine_street = document.value=address[2];


        var is_value = 0;
        document.getElementById('shipping_inpostparcels'+sufix).value = box_machine_name;
        var shipping_inpostparcels = document.getElementById('shipping_inpostparcels'+sufix);

	for(i = 0; i < shipping_inpostparcels.length; i++)
	{
		if(shipping_inpostparcels.options[i].value == document.getElementById('name').value)
		{
			shipping_inpostparcels.selectedIndex = i;
			is_value = 1;
		}
        }

	if (is_value == 0)
	{
		shipping_inpostparcels.options[shipping_inpostparcels.options.length] = new Option(box_machine_name+','+box_machine_town+','+box_machine_street, box_machine_name);
		shipping_inpostparcels.selectedIndex = shipping_inpostparcels.length-1;
        }
}
</script>

<form name='adminForm' id="adminForm" method="POST">
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="view" value="inpostparcels" />
    <?php echo JHTML::_( 'form.token' ); ?>
    <input type="hidden" name="parcel_id" value="<?php echo $this->inpostparcelsData['parcel_id']; ?>" />
    <input type="hidden" name="id" value="<?php echo $this->inpostparcelsData['id']; ?>" />

<table class="adminlist table table-striped" style="table-layout: fixed;">

    <?php if(in_array($this->parcel->api_source, array('PL'))): ?>
    <tr>
        <td width="150" align="right"><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_COD_AMOUNT') ?>:</td>
	<td>
		<input name="parcel_cod_amount" type="text" value="<?php echo $this->inpostparcelsData['parcel_cod_amount']; ?>" <?php echo $this->disabledCodAmount; ?> <?php ?>/>
	</td>
    </tr>
    <?php endif; ?>

    <tr>
	<td class="key" style="width:150px;">
		<label for="parcel_description"><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_DESCRIPTION') ?> *
		</label>
	</td>
        <td><textarea name="parcel_description" rows="10" cols="10" <?php echo $this->disabledDescription; ?> required="required" class="required"><?php echo $this->inpostparcelsData['parcel_description']; ?></textarea></td>
    </tr>

    <?php if(in_array($this->parcel->api_source, array('PL'))): ?>
    <tr>
        <td align="right"><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_INSURANCE_AMOUNT') ?>:</td>
        <td>
            <select id="parcel_insurance_amount" name="parcel_insurance_amount" <?php echo $this->disabledInsuranceAmount; ?>>
                <option value='' <?php if(@$this->inpostparcelsData['parcel_insurance_amount'] == ''){ echo "selected=selected";} ?>><?php echo $this->defaultInsuranceAmount; ?></option>
                <?php foreach($this->parcelInsurancesAmount as $key => $parcelInsuranceAmount): ?>
                <option value='<?php echo $key ?>' <?php if($this->inpostparcelsData['parcel_insurance_amount'] == $key){ echo "selected=selected";} ?>><?php echo $parcelInsuranceAmount;?></option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php endif; ?>

    <tr>
	<td class="key">
		<label for="parcel_receiver_phone"><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_RECEIVER_PHONE') ?> *
		</label>
	</td>
	<td>
		<input id="parcel_receiver_phone" name="parcel_receiver_phone" type="text" value="<?php echo $this->inpostparcelsData['parcel_receiver_phone']; ?>" <?php echo $this->disabledReceiverPhone; ?> required="required" maxlength="9" class="required" />
	</td>
    </tr>

    <tr>
	<td class="key">
		<label for="parcel_receiver_email"><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_RECEIVER_EMAIL') ?> *
		</label>
	</td>
	<td>
		<input id="parcel_receiver_email" name="parcel_receiver_email" type="text" value="<?php echo $this->inpostparcelsData['parcel_receiver_email']; ?>" <?php echo $this->disabledReceiverEmail; ?> required="required" class="required" />
	</td>
    </tr>

    <tr>
	<td class="key">
		<label for="parcel_size"><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SIZE') ?> *
		</label>
	</td>
        <td>
            <select id="parcel_size" name="parcel_size" <?php echo $this->disabledParcelSize; ?> required="required" class="required">
                <option value='' <?php if($this->inpostparcelsData['parcel_size'] == ''){ echo "selected=selected";} ?>><?php echo $this->defaultParcelSize;?></option>
                <option value='<?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SIZE_A') ?>' <?php if($this->inpostparcelsData['parcel_size'] == JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SIZE_A')){ echo "selected=selected";} ?>><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SIZE_A') ?></option>
                <option value='<?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SIZE_B') ?>' <?php if($this->inpostparcelsData['parcel_size'] == JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SIZE_B')){ echo "selected=selected";} ?>><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SIZE_B') ?></option>
                <option value='<?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SIZE_C') ?>' <?php if($this->inpostparcelsData['parcel_size'] == JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SIZE_C')){ echo "selected=selected";} ?>><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SIZE_C') ?></option>
            </select>
        </td>
    </tr>

    <?php if(@$this->inpostparcelsData['parcel_status'] != '0'): ?>
    <tr>
	<td class="key">
		<label for="parcel_status"><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_STATUS') ?></label>
	</td>
	<td>
		<input id="parcel_status" name="parcel_status" type="text" value="<?php echo $this->inpostparcelsData['parcel_status']; ?>" <?php echo $this->disabledParcelStatus; ?>/>
	</td>
    </tr>
    <?php endif; ?>

    <?php if(in_array($this->parcel->api_source, array('PL'))): ?>
    <tr>
	<td class="key">
		<label for="shipping_inpostparcels_source"><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SOURCE_MACHINE') ?></label>
		</td>
        <td>
            <select id="shipping_inpostparcels_source" name="parcel_source_machine_id" <?php echo $this->disabledSourceMachine; ?>>
                <option value='' <?php if(@$this->inpostparcelsData['parcel_source_machine_id'] == ''){ echo "selected=selected";} ?>><?php echo $this->defaultSourceMachine;?></option>
                <?php foreach($this->parcelSourceMachinesId as $key => $parcelSourceMachine): ?>
                <option value='<?php echo $key ?>' <?php if($this->inpostparcelsData['parcel_source_machine_id'] == $key){ echo "selected=selected";} ?>><?php echo $parcelSourceMachine;?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" id="name_source" name="name_source" disabled="disabled" />
            <input type="hidden" id="box_machine_town_source" name="box_machine_town_source" disabled="disabled" />
            <input type="hidden" id="address_source" name="address_source" disabled="disabled" />
            <a href="#" onclick="openMap('source_machine'); return false;"><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_MAP') ?></a>
            &nbsp;|&nbsp;<input type="checkbox" name="show_all_machines_source" <?php echo $this->disabledSourceMachine; ?>> <?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SHOW_TERMINAL') ?>
        </td>
    </tr>
    <?php endif; ?>

    <tr>
	<td class="key">
		<label for="parcel_tmp_id"><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_TMP_ID') ?> *
		</label>
	</td>
        <td><input id="parcel_tmp_id" name="parcel_tmp_id" type="text" value="<?php echo $this->inpostparcelsData['parcel_tmp_id']; ?>" <?php echo $this->disabledTmpId; ?> required="required" class="required"/></td>
    </tr>

    <tr>
	<td class="key">
		<label for="shipping_inpostparcels"><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_TARGET_MACHINE') ?> *</label>
	</td>
        <td>
            <select class="chzn-done" id="shipping_inpostparcels" name="parcel_target_machine_id" <?php echo $this->disabledTargetMachine; ?>>
                <option value='' <?php if(@$this->inpostparcelsData['parcel_target_machine_id'] == ''){ echo "selected=selected";} ?>><?php echo $this->defaultTargetMachine;?></option>
                <?php foreach($this->parcelTargetMachinesId as $key => $parcelTargetMachine): ?>
                <option value='<?php echo $key ?>' <?php if($this->inpostparcelsData['parcel_target_machine_id'] == $key){ echo "selected=selected";} ?>><?php echo $parcelTargetMachine;?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" id="name" name="name" disabled="disabled" />
            <input type="hidden" id="box_machine_town" name="box_machine_town" disabled="disabled" />
            <input type="hidden" id="address" name="address" disabled="disabled" />
            <a href="#" onclick="openMap('target_machine'); return false;"><?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_MAP') ?></a>
            &nbsp;|&nbsp;<input type="checkbox" name="show_all_machines" <?php echo $this->disabledTargetMachine; ?>> <?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SHOW_TERMINAL') ?>
        </td>
    </tr>

</table>

</form>

<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('input[type="checkbox"][name="show_all_machines"]').click(function(){
            var machines_list_type = jQuery(this).is(':checked');

	    if(machines_list_type == true)
	    {
                //alert('all machines');
                var machines = {
                    '' : '<?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SELECT_MACHINE');?>',
                <?php foreach($this->parcelTargetAllMachinesId as $key => $parcelTargetAllMachineId): ?>
                    '<?php echo $key ?>' : '<?php echo addslashes($parcelTargetAllMachineId) ?>',
                    <?php endforeach; ?>
                };
	    }
	    else
	    {
                //alert('criteria machines');
                var machines = {
                    '' : '<?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SELECT_MACHINE');?>',
                <?php foreach($this->parcelTargetMachinesId as $key => $parcelTargetMachineId): ?>
                    '<?php echo $key ?>' : '<?php echo addslashes($parcelTargetMachineId) ?>',
                    <?php endforeach; ?>
                };
            }

            jQuery('#shipping_inpostparcels option').remove();
            jQuery.each(machines, function(val, text) {
                jQuery('#shipping_inpostparcels').append(
                        jQuery('<option></option>').val(val).html(text)
                );
            });
        });
        jQuery('input[type="checkbox"][name="show_all_machines_source"]').click(function(){
            var machines_list_type = jQuery(this).is(':checked');

            if(machines_list_type == true){
                //alert('all machines');
                var machines = {
                    '' : '<?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SELECT_MACHINE');?>',
		<?php if (isset($this->parcelSourceMachinesId) &&
			$this->parcelSourceMachinesId != null &&
			$this->parcelSourceMachinesId != "null") : ?>
                <?php foreach($this->parcelSourceAllMachinesId as $key => $parcelSourceAllMachineId): ?>
                    '<?php echo $key; ?>' : '<?php echo addslashes($parcelSourceAllMachineId); ?>',
                    <?php endforeach; ?>
		<?php endif; ?>
                };
            }else{
                //alert('criteria machines');
                var machines = {
                    '' : '<?php echo JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SELECT_MACHINE');?>',
		<?php if (isset($this->parcelSourceMachinesId) &&
			$this->parcelSourceMachinesId != null &&
			$this->parcelSourceMachinesId != "null") : ?>
			<?php foreach($this->parcelSourceMachinesId as $key => $parcelSourceMachineId): ?>
                    '<?php echo $key; ?>' : '<?php echo addslashes($parcelSourceMachineId); ?>',
			<?php endforeach; ?>
		<?php endif; ?>
                };
            }

            jQuery('#shipping_inpostparcels_source option').remove();
            jQuery.each(machines, function(val, text) {
                jQuery('#shipping_inpostparcels_source').append(
                        jQuery('<option></option>').val(val).html(text)
                );
            });
        });
    });
</script>

<?php
AdminUIHelper::imitateTabs('end');
AdminUIHelper::endAdminArea(); ?>
