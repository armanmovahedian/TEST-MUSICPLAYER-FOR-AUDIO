jQuery(document).ready(function($) {
    // Populate filter dropdowns
    function populateFilters() {
        var uniqueAttributes = aap_playlist_data.unique_attributes;
        for (var attribute in uniqueAttributes) {
            if (uniqueAttributes.hasOwnProperty(attribute)) {
                var select = $('.aap-filter[data-attribute="' + attribute + '"]');
                var options = uniqueAttributes[attribute];
                if (select.length && options.length) {
                    options.forEach(function(option) {
                        select.append('<option value="' + option + '">' + option + '</option>');
                    });
                }
            }
        }
    }

    populateFilters();

    // Filter logic
    $('.aap-filter').on('change', function() {
        var filters = {};
        $('.aap-filter').each(function() {
            var attribute = $(this).data('attribute');
            var value = $(this).val();
            if (value) {
                filters[attribute] = value;
            }
        });

        $('.aap-playlist-item').each(function() {
            var item = $(this);
            var show = true;
            for (var attribute in filters) {
                if (filters.hasOwnProperty(attribute)) {
                    if (item.data(attribute) != filters[attribute]) {
                        show = false;
                        break;
                    }
                }
            }

            if (show) {
                item.show();
            } else {
                item.hide();
            }
        });
    });
});
