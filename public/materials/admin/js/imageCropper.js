(function (window, document, $) {
    'use strict';

    if (!$) {
        return;
    }

    const modalId = 'adminImageCropperModal';
    let state = null;
    let isInitialized = false;

    function ensureModal() {
        if (document.getElementById(modalId)) {
            return;
        }

        const html = '' +
            '<div class="admin-cropper-modal" id="' + modalId + '" aria-hidden="true">' +
                '<div class="admin-cropper-card">' +
                    '<div class="admin-cropper-head">' +
                        '<h3>Crop Image</h3>' +
                        '<button type="button" class="admin-cropper-close" data-cropper-close aria-label="Close">x</button>' +
                    '</div>' +
                    '<div class="admin-cropper-body">' +
                        '<canvas id="adminCropperCanvas" width="360" height="360"></canvas>' +
                        '<div class="admin-cropper-controls">' +
                            '<label for="adminCropperZoom">Zoom</label>' +
                            '<input type="range" id="adminCropperZoom" min="1" max="3" step="0.01" value="1">' +
                        '</div>' +
                        '<div class="admin-cropper-actions">' +
                            '<button type="button" class="admin-cropper-btn secondary" data-cropper-cancel>Cancel</button>' +
                            '<button type="button" class="admin-cropper-btn primary" data-cropper-apply>Apply Crop</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        $('body').append(html);
    }

    function getModalNodes() {
        const modal = $('#' + modalId);
        const canvas = document.getElementById('adminCropperCanvas');
        return {
            modal: modal,
            canvas: canvas,
            ctx: canvas ? canvas.getContext('2d') : null,
            zoom: $('#adminCropperZoom')
        };
    }

    function defaultErrorWriter(errorSelector, message) {
        if (!errorSelector) {
            return;
        }
        $(errorSelector).text(message || '');
    }

    function draw() {
        if (!state || !state.ctx || !state.image) {
            return;
        }

        const ctx = state.ctx;
        const width = state.canvasSize;
        const height = state.canvasHeight;
        const image = state.image;

        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#f4f8fc';
        ctx.fillRect(0, 0, width, height);

        const drawWidth = image.width * state.scale;
        const drawHeight = image.height * state.scale;
        ctx.drawImage(image, state.offsetX, state.offsetY, drawWidth, drawHeight);
    }

    function closeModal(clearInputOnClose) {
        const nodes = getModalNodes();
        nodes.modal.removeClass('active').attr('aria-hidden', 'true');

        if (clearInputOnClose && state && state.input) {
            state.input.value = '';
        }

        state = null;
    }

    function setInputFileAndNotify(input, blob, fileName) {
        const file = new File([blob], fileName, {
            type: blob.type || 'image/png',
            lastModified: Date.now()
        });

        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;

        input.dataset.cropperApplied = '1';
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function openForFile(config, file) {
        const nodes = getModalNodes();
        if (!nodes.canvas || !nodes.ctx) {
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        const image = new Image();

        image.onload = function () {
            const ratio = Number(config.aspectRatio || 1);
            const size = 360;
            nodes.canvas.width = size;
            nodes.canvas.height = Math.round(size / ratio);

            const canvasWidth = nodes.canvas.width;
            const canvasHeight = nodes.canvas.height;
            const minScale = Math.max(canvasWidth / image.width, canvasHeight / image.height);

            state = {
                config: config,
                input: config.input,
                image: image,
                canvas: nodes.canvas,
                ctx: nodes.ctx,
                canvasSize: canvasWidth,
                canvasHeight: canvasHeight,
                minScale: minScale,
                scale: minScale,
                offsetX: (canvasWidth - (image.width * minScale)) / 2,
                offsetY: (canvasHeight - (image.height * minScale)) / 2,
                dragging: false,
                dragStartX: 0,
                dragStartY: 0
            };

            nodes.zoom.val('1');
            draw();
            nodes.modal.addClass('active').attr('aria-hidden', 'false');
            URL.revokeObjectURL(objectUrl);
        };

        image.onerror = function () {
            URL.revokeObjectURL(objectUrl);
            config.writeError('Unable to read selected image.');
            config.input.value = '';
        };

        image.src = objectUrl;
    }

    function initEvents() {
        if (isInitialized) {
            return;
        }
        isInitialized = true;

        const nodes = getModalNodes();
        if (!nodes.canvas) {
            return;
        }

        $('#' + modalId).on('click', function (event) {
            if (event.target === this) {
                closeModal(true);
            }
        });

        $(document).on('click', '[data-cropper-close], [data-cropper-cancel]', function () {
            closeModal(true);
        });

        $(document).on('click', '[data-cropper-apply]', function () {
            if (!state || !state.canvas) {
                closeModal(false);
                return;
            }

            const outputType = state.config.outputType || 'image/png';
            const outputQuality = Number(state.config.outputQuality || 0.92);
            const fileName = state.config.outputFileName || 'cropped-image.png';

            state.canvas.toBlob(function (blob) {
                if (!blob) {
                    state.config.writeError('Unable to crop image.');
                    return;
                }

                setInputFileAndNotify(state.input, blob, fileName);

                if (typeof state.config.onCropped === 'function') {
                    state.config.onCropped(blob);
                }

                closeModal(false);
            }, outputType, outputQuality);
        });

        nodes.zoom.on('input', function () {
            if (!state || !state.image) {
                return;
            }

            const zoomMultiplier = parseFloat($(this).val() || '1');
            const centerX = state.canvas.width / 2;
            const centerY = state.canvas.height / 2;
            const previousScale = state.scale;
            state.scale = state.minScale * zoomMultiplier;

            const relX = (centerX - state.offsetX) / previousScale;
            const relY = (centerY - state.offsetY) / previousScale;
            state.offsetX = centerX - relX * state.scale;
            state.offsetY = centerY - relY * state.scale;

            draw();
        });

        nodes.canvas.addEventListener('mousedown', function (event) {
            if (!state) {
                return;
            }
            state.dragging = true;
            state.dragStartX = event.clientX - state.offsetX;
            state.dragStartY = event.clientY - state.offsetY;
        });

        window.addEventListener('mousemove', function (event) {
            if (!state || !state.dragging) {
                return;
            }
            state.offsetX = event.clientX - state.dragStartX;
            state.offsetY = event.clientY - state.dragStartY;
            draw();
        });

        window.addEventListener('mouseup', function () {
            if (state) {
                state.dragging = false;
            }
        });
    }

    function normalizeConfig(input, config) {
        const normalized = $.extend({
            allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'],
            maxSizeBytes: 2 * 1024 * 1024,
            aspectRatio: 1,
            outputType: 'image/png',
            outputQuality: 0.92,
            outputFileName: 'cropped-image.png',
            errorSelector: '',
            onCropped: null
        }, config || {});

        normalized.input = input;
        normalized.writeError = function (message) {
            defaultErrorWriter(normalized.errorSelector, message);
        };

        return normalized;
    }

    function attach(inputSelector, config) {
        ensureModal();
        initEvents();

        $(document).on('change', inputSelector, function () {
            if (this.dataset.cropperApplied === '1') {
                this.dataset.cropperApplied = '';
                return;
            }

            const file = this.files && this.files[0] ? this.files[0] : null;
            const normalized = normalizeConfig(this, config);
            normalized.writeError('');

            if (!file) {
                return;
            }

            const type = String(file.type || '').toLowerCase();
            if (normalized.allowedTypes.indexOf(type) === -1) {
                normalized.writeError('Unsupported image format.');
                this.value = '';
                return;
            }

            if (file.size > normalized.maxSizeBytes) {
                normalized.writeError('Image size is too large.');
                this.value = '';
                return;
            }

            openForFile(normalized, file);
        });
    }

    window.AdminImageCropper = {
        attach: attach
    };
})(window, document, window.jQuery);
