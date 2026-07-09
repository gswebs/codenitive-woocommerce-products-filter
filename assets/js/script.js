document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.codenit-wc-apf-form').forEach(form => {

        form.addEventListener('submit', function (e) {
            // Dropdowns can use standard GET form behavior.
            if (form.querySelector('select')) {
                return;
            }

            e.preventDefault();
            const params = new URLSearchParams();

            // Preserve hidden query args such as orderby/search values.
            form.querySelectorAll('input[type="hidden"]').forEach(input => {
                if (!input.name || !input.value) return;
                params.set(input.name, input.value);
            });

            // Handle checkboxes for attributes, product categories, and product tags.
            form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                if (!cb.checked) return;

                const attr = cb.dataset.attribute;
                const value = cb.value;

                if (!attr || !value) return;

                if (params.has(attr)) {
                    params.set(attr, params.get(attr) + ',' + value);
                } else {
                    params.set(attr, value);
                }
            });

            // Handle price slider.
            const minPrice = form.querySelector('input[name="min_price"]');
            const maxPrice = form.querySelector('input[name="max_price"]');

            if (minPrice && maxPrice) {
                params.set('min_price', minPrice.value);
                params.set('max_price', maxPrice.value);
            }

            params.delete('paged');
            params.delete('product-page');

            const queryString = params.toString();
            window.location.href = queryString
                ? window.location.pathname + '?' + queryString
                : window.location.pathname;
        });

        const minInput = form.querySelector('input[name="min_price"]');
        const maxInput = form.querySelector('input[name="max_price"]');
        const minText  = form.querySelector('#min-price-text');
        const maxText  = form.querySelector('#max-price-text');

        if (!minInput || !maxInput) return;

        function updateSlider() {
            if (parseInt(minInput.value, 10) > parseInt(maxInput.value, 10)) {
                let tmp = minInput.value;
                minInput.value = maxInput.value;
                maxInput.value = tmp;
            }

            if (minText) minText.innerText = minInput.value;
            if (maxText) maxText.innerText = maxInput.value;
        }

        minInput.addEventListener('input', updateSlider);
        maxInput.addEventListener('input', updateSlider);

    });
});

(function () {

    let css_var = {
        contentPadding: "0px",
        inactiveContentPadding: "0px",
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Set max-height and padding for default active items.
        document.querySelectorAll('.codenit-filter.codenit-accordion-active').forEach(item => {
            const content = item.querySelector('.codenit-accordion-content');
            if (!content) return;

            content.style.maxHeight = content.scrollHeight + "px";
            content.style.paddingBlock = css_var.contentPadding;

            const header = item.querySelector('.codenit-accordion-header');
            if (header) header.setAttribute('aria-expanded', true);
        });
    });

    document.addEventListener('click', function (e) {
        const header = e.target.closest('.codenit-accordion-header');
        if (!header) return;

        const item = header.closest('.codenit-filter');
        if (!item) return;

        const wrap = item.closest('.product-filter-inner');
        if (!wrap) return;

        // Close other items.
        wrap.querySelectorAll('.codenit-filter').forEach(el => {
            if (el !== item) {
                el.classList.remove('codenit-accordion-active');
                const otherContent = el.querySelector('.codenit-accordion-content');

                if (otherContent) {
                    otherContent.style.maxHeight = null;
                    otherContent.style.paddingBlock = css_var.inactiveContentPadding;
                }

                const otherHeader = el.querySelector('.codenit-accordion-header');
                if (otherHeader) otherHeader.setAttribute('aria-expanded', false);
            }
        });

        // Toggle current item.
        item.classList.toggle('codenit-accordion-active');
        const content = item.querySelector('.codenit-accordion-content');
        if (!content) return;

        if (item.classList.contains('codenit-accordion-active')) {
            requestAnimationFrame(() => {
                content.style.maxHeight = content.scrollHeight + "px";
                content.style.paddingBlock = css_var.contentPadding;
            });
            header.setAttribute('aria-expanded', true);
        } else {
            requestAnimationFrame(() => {
                content.style.maxHeight = null;
                content.style.paddingBlock = css_var.inactiveContentPadding;
            });
            header.setAttribute('aria-expanded', false);
        }

    });
})();
