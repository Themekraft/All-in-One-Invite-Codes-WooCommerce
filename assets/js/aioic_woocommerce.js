jQuery(document).ready(function ($) {

   
    $( document).on('click','#aioic_sell_invite_codes_chbx',this,function(event){
        
            $("#aioic_generate_invite_codes").toggle(this.checked);
            if(this.checked){
                $("#_virtual").prop('checked', true);
            }
            else{
                $("#_virtual").prop('checked', false);
            }
       


    })
})