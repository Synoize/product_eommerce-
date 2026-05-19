(function() {
    function createStatus(input) {
        var status = input.parentElement.querySelector('[data-pincode-status]');

        if (!status) {
            status = document.createElement('p');
            status.setAttribute('data-pincode-status', 'true');
            status.className = 'mt-1 text-xs';
            input.insertAdjacentElement('afterend', status);
        }

        return status;
    }

    function setStatus(status, message, type) {
        status.textContent = message || '';
        status.className = 'mt-1 text-xs ' + (
            type === 'success' ? 'text-green-600' :
            type === 'error' ? 'text-red-600' :
            'text-gray-500'
        );
    }

    function attachPincodeLookup(form) {
        var input = form.querySelector('input[name="pincode"]');
        var cityInput = form.querySelector('input[name="city"]');
        var stateInput = form.querySelector('input[name="state"]');
        var submitButton = form.querySelector('button[type="submit"]');
        var apiUrl = form.getAttribute('data-pincode-api');

        if (!input || !apiUrl) {
            return;
        }

        var status = createStatus(input);
        var verifiedPincode = '';
        var verifying = false;
        var activeVerification = null;
        var debounceTimer = null;

        function markPending() {
            verifiedPincode = '';
            input.setCustomValidity('Please verify this pincode.');
        }

        function verifyPincode() {
            var pincode = input.value.replace(/[^0-9]/g, '').slice(0, 6);
            input.value = pincode;

            if (pincode.length === 0) {
                verifiedPincode = '';
                input.setCustomValidity('');
                setStatus(status, '', 'neutral');
                return Promise.resolve(false);
            }

            if (pincode.length !== 6 || pincode[0] === '0') {
                markPending();
                setStatus(status, 'Enter a valid 6-digit Indian pincode.', 'error');
                return Promise.resolve(false);
            }

            if (verifiedPincode === pincode) {
                input.setCustomValidity('');
                return Promise.resolve(true);
            }

            verifying = true;
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('opacity-60', 'cursor-not-allowed');
            }
            setStatus(status, 'Checking pincode...', 'neutral');

            activeVerification = fetch(apiUrl + '?pincode=' + encodeURIComponent(pincode), {
                headers: { 'Accept': 'application/json' }
            })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (!data.valid) {
                        verifiedPincode = '';
                        input.setCustomValidity(data.message || 'Invalid pincode.');
                        setStatus(status, data.message || 'Invalid pincode.', 'error');
                        return false;
                    }

                    verifiedPincode = pincode;
                    input.setCustomValidity('');
                    if (cityInput && data.city) {
                        cityInput.value = data.city;
                    }
                    if (stateInput && data.state) {
                        stateInput.value = data.state;
                    }
                    setStatus(status, 'Pincode verified: ' + [data.city, data.state].filter(Boolean).join(', '), 'success');
                    return true;
                })
                .catch(function() {
                    verifiedPincode = '';
                    input.setCustomValidity('Could not verify pincode right now. Please try again.');
                    setStatus(status, 'Could not verify pincode right now. Please try again.', 'error');
                    return false;
                })
                .finally(function() {
                    verifying = false;
                    activeVerification = null;
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.classList.remove('opacity-60', 'cursor-not-allowed');
                    }
                });

            return activeVerification;
        }

        input.addEventListener('input', function() {
            input.value = input.value.replace(/[^0-9]/g, '').slice(0, 6);
            clearTimeout(debounceTimer);

            if (input.value.length === 0) {
                verifiedPincode = '';
                input.setCustomValidity(input.required ? 'Pincode is required.' : '');
                setStatus(status, '', 'neutral');
                return;
            }

            markPending();
            debounceTimer = setTimeout(verifyPincode, input.value.length === 6 ? 250 : 500);
        });

        input.addEventListener('blur', verifyPincode);

        form.addEventListener('submit', function(event) {
            if (verifiedPincode === input.value) {
                return;
            }

            event.preventDefault();
            (activeVerification || verifyPincode()).then(function(valid) {
                if (valid) {
                    form.requestSubmit();
                } else {
                    input.reportValidity();
                    input.focus();
                }
            });
        });

        if (input.value.length === 6) {
            verifyPincode();
        }
    }

    document.querySelectorAll('form[data-pincode-api]').forEach(attachPincodeLookup);
})();
