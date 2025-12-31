document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.codenit-wc-apf-widget-form').forEach(form => {

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const params = new URLSearchParams();

            form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                if (!cb.checked) return;

                const attr  = cb.dataset.attribute;
                const value = cb.value;

                if (params.has(attr)) {
                    params.set(attr, params.get(attr) + ',' + value);
                } else {
                    params.set(attr, value);
                }
            });

            // O filters selected → clean URL
            if ([...params].length === 0) {
                window.location.href = window.location.pathname;
                return;
            }

            // Filters selected
            window.location.href =
                window.location.pathname + '?' + params.toString();
        });

    });

});
