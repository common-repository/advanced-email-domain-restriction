jQuery(document).ready(function($) {
    function addEntry(field) {
        var newRow = $('<tr>');
        var fieldType = (field === 'aedr_custom_messages') ? 'text' : 'text';
        var inputField = '<input type="' + fieldType + '" name="aedr_settings[' + field + '][]" placeholder="' + aedrLocalized.aedr_placeholder_domain_text + '" required>';
        var messageField = (field === 'aedr_custom_messages') ? '<td><input type="text" name="aedr_settings[' + field + '_message][]" placeholder="' + aedrLocalized.aedr_placeholder_message_text + '"></td>' : '';
        newRow.append('<td>' + inputField + '</td>' + messageField + '<td><button type="button" class="button aedr-remove-entry">' + aedrLocalized.aedr_remove_button_text + '</button></td>');
        $('#aedr-' + field + '-list').append(newRow);
    }

    $('.aedr-add-entry').on('click', function() {
        var field = $(this).data('field');
        addEntry(field);
    });

    $(document).on('click', '.aedr-remove-entry', function() {
        $(this).closest('tr').remove();
    });
});

