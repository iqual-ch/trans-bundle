if (window.jQuery) {
    $('input#query').on('keyup', function () {
        setCheckFiltersState($(this));
    });
    
    $(function () {
        setCheckFiltersState($('input#query'));
    });
    
    function setCheckFiltersState(input) {
        var elems = input.closest('tr').next().find('input:checkbox');
        if (input.val().trim() === '') {
            elems.attr('disabled', true);
        } else {
            elems.attr('disabled', false);
        }
    }
} else if (window.console) {
    console.error('jQuery is required for that bundle.');
}