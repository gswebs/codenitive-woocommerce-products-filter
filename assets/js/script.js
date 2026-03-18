document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.codenit-wc-apf-widget-form').forEach(form => {

        form.addEventListener('submit', function (e) {
            //If dropdown exists, allow normal form submit (standard GET behavior)
            if (form.querySelector('select')) {
                return;
            }

            e.preventDefault();
            const params = new URLSearchParams();

            //Handle Checkboxes (Attributes)
            form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                if (!cb.checked) return;
                const attr = cb.dataset.attribute;
                const value = cb.value;

                if (params.has(attr)) {
                    params.set(attr, params.get(attr) + ',' + value);
                } else {
                    params.set(attr, value);
                }
            });

            //Handle Price Slider (Range inputs)
            const minPrice = form.querySelector('input[name="min_price"]');
            const maxPrice = form.querySelector('input[name="max_price"]');
            
            if (minPrice && maxPrice) {
                params.set('min_price', minPrice.value);
                params.set('max_price', maxPrice.value);
            }

            // 3. Redirect logic
            const queryString = params.toString();
            window.location.href = queryString 
                ? window.location.pathname + '?' + queryString 
                : window.location.pathname;
        });

        const minInput = document.getElementById('min_range');
        const maxInput = document.getElementById('max_range');
        const minText  = document.getElementById('min-price-text');
        const maxText  = document.getElementById('max-price-text');
    
        if (!minInput || !maxInput) return;
    
        function updateSlider() {
            if (parseInt(minInput.value) > parseInt(maxInput.value)) {
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


/*document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.codenit-wc-apf-widget-form').forEach(form => {

        form.addEventListener('submit', function (e) {

            // If dropdown exists, allow normal form submit
            if (form.querySelector('select')) {
                return;
            }

            // checkbox-only handling
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

            // No filters → clean URL
            if ([...params].length === 0) {
                window.location.href = window.location.pathname;
                return;
            }

            // Apply filters
            window.location.href =
                window.location.pathname + '?' + params.toString();
        });

    });

});*/

(function () {

    let css_var = {
        contentPadding: "0px",
        inactiveContentPadding: "0px",
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Set max-height and padding for default active items
        document.querySelectorAll('.codenit-filter.codenit-accordion-active').forEach(item => {
            const content = item.querySelector('.codenit-accordion-content');
            content.style.maxHeight = content.scrollHeight + "px";
            content.style.paddingBlock = css_var.contentPadding;
            //content.style.opacity = "1";

            const header = item.querySelector('.codenit-accordion-header');
            if (header) header.setAttribute('aria-expanded', true);
        });
    });

    document.addEventListener('click', function (e) {
        const header = e.target.closest('.codenit-accordion-header');
        if (!header) return;

        const item = header.closest('.codenit-filter');
        const wrap = item.closest('.product-filter-inner');

        // Close other items (only allow one open)
        wrap.querySelectorAll('.codenit-filter').forEach(el => {
            if (el !== item) {
                el.classList.remove('codenit-accordion-active');
                const otherContent = el.querySelector('.codenit-accordion-content');
                otherContent.style.maxHeight = null;
                otherContent.style.paddingBlock = css_var.inactiveContentPadding;
                
                const otherHeader = el.querySelector('.codenit-accordion-header');
                if (otherHeader) otherHeader.setAttribute('aria-expanded', false);
            }
        });

        // Toggle current item
        item.classList.toggle('codenit-accordion-active');
        const content = item.querySelector('.codenit-accordion-content');

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

