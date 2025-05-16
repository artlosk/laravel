// resources/js/mediaUtils.js

// Настройка Toastr
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "5000"
};

// Инициализация FilePond
export function initializeFilePond(inputSelector) {
    try {
        FilePond.registerPlugin(
            FilePondPluginImagePreview,
            FilePondPluginFileValidateType,
            FilePondPluginFileValidateSize
        );

        const inputElement = document.querySelector(inputSelector);
        if (!inputElement) {
            console.error('FilePond input element not found.');
            return null;
        }

        const pond = FilePond.create(inputElement, {
            allowMultiple: true,
            maxFiles: 5,
            name: 'filepond[]',
            server: {
                process: window.appConfig.routes.filepondUpload,
                revert: window.appConfig.routes.filepondDelete,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                onload: (response) => response,
                ondata: (formData) => formData,
                onerror: (response) => {
                    let errorMessage = 'Произошла ошибка на сервере.';
                    if (response && response.data && response.data.error) {
                        errorMessage = response.data.error;
                    } else if (response && response.data && response.data.errors) {
                        errorMessage = 'Ошибки валидации: ' + Object.values(response.data.errors).flat().join(', ');
                    }
                    toastr.error(errorMessage);
                    return errorMessage;
                }
            },
            labelIdle: 'Перетащите файлы сюда или <span class="filepond--label-action"> выберите </span>',
            labelInvalidField: 'Поле содержит недопустимые файлы',
            labelFileWaitingForSize: 'Ожидание размера',
            labelFileSizeNotAvailable: 'Размер недоступен',
            labelFileLoading: 'Загрузка',
            labelFileLoadError: 'Ошибка при загрузке',
            labelFileProcessing: 'Загрузка',
            labelFileProcessingComplete: 'Загрузка завершена',
            labelFileProcessingAborted: 'Загрузка отменена',
            labelFileProcessingError: 'Ошибка при загрузке',
            labelFileRemoveError: 'Ошибка при удалении',
            labelTapToCancel: 'нажмите для отмены',
            labelTapToRetry: 'нажмите для повтора',
            labelTapToUndo: 'нажмите для отмены последнего действия',
            labelButtonRemoveItem: 'Удалить',
            labelButtonAbortItemLoad: 'Отменить загрузку',
            labelButtonRetryItemLoad: 'Повторить загрузку',
            labelButtonAbortItemProcessing: 'Отменить загрузку',
            labelButtonUndoItemProcessing: 'Отменить последнее действие',
            labelButtonRetryItemProcessing: 'Повторить загрузить',
            labelButtonProcessItem: 'Загрузить',
            labelMaxFilesExceeded: 'Превышено максимальное количество файлов ({maxFiles})',
            labelFileValidateTypeLabelExpectedTypes: 'Ожидаются файлы типа {allButLastType} или {lastType}',
            labelFileValidateTypeDescription: 'Недопустимый тип файла',
            labelFileValidateSizeLabelExpectedSize: 'Ожидается размер {filesize}',
            labelFileValidateSizeLabelMaxFileSize: 'Максимальный размер файла {filesSize}',
            labelFileValidateSizeDescription: 'Файл слишком большой',
        });

        pond.on('processfile', (error, file) => {
            if (error) return;

            const temporaryFileId = file.serverId;
            if (temporaryFileId) {
                const $previewContainer = $('#selectedMediaPreview');
                const isImage = file.fileType && file.fileType.startsWith('image/');
                const previewUrl = isImage ? URL.createObjectURL(file.file) : null;

                $previewContainer.append(`
                    <div class="media-preview-item col-auto p-0 mr-2 mb-2" data-media-id="${temporaryFileId}">
                        <button type="button" class="remove-btn" data-media-id="${temporaryFileId}">×</button>
                        <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 80px; height: 80px; overflow: hidden;">
                            ${isImage ? `<img src="${previewUrl}" class="img-thumbnail rounded" alt="${file.file.name}">` : `<i class="fas fa-file fa-3x text-muted"></i>`}
                        </div>
                        <small class="d-block text-truncate mt-1" title="${file.file.name}">${file.file.name}</small>
                    </div>
                `);

                updateMediaOrder($previewContainer);
                if (previewUrl) file.ready = true;
            } else {
                toastr.error('Ошибка: не получен временный ID файла от сервера.');
            }
        });

        pond.on('processfilerevert', (file) => {
            const temporaryFileId = file.serverId;
            if (temporaryFileId) {
                $(`#selectedMediaPreview .media-preview-item[data-media-id="${temporaryFileId}"]`).remove();
                updateMediaOrder($('#selectedMediaPreview'));
            }
        });

        return pond;
    } catch (error) {
        console.error('Error initializing FilePond:', error);
        toastr.error('Ошибка инициализации FilePond: ' + error.message);
        return null;
    }
}

// Загрузка контента модального окна
export function loadMediaLibraryModal(modalSelector, contentSelector) {
    console.log('loadMediaLibraryModal initialized, modalSelector:', modalSelector);
    console.log('appConfig:', window.appConfig);
    console.log('Gallery button exists:', $('[data-target="#mediaLibraryModal"]').length);

    // Делегирование события клика на документ
    $(document).on('click', '[data-target="#mediaLibraryModal"], [data-toggle="modal"][data-target="#mediaLibraryModal"]', function (e) {
        console.log('Gallery button clicked');
        e.preventDefault(); // Предотвращаем стандартное поведение, если нужно
        loadMediaContent(window.appConfig.routes.mediaIndex);
    });

    function loadMediaContent(url) {
        console.log('Loading media content from URL:', url);
        $.get(url, function (data) {
            console.log('Media content loaded successfully');
            $(contentSelector).html(data);
            addMediaItemClickHandlers(contentSelector);
            restoreSelectedMediaInModal();
            initModalSortable(contentSelector);
            initMediaDeletion(contentSelector);
            $(contentSelector).find('.pagination a').on('click', function (e) {
                e.preventDefault();
                console.log('Pagination link clicked:', $(this).attr('href'));
                loadMediaContent($(this).attr('href'));
            });
        }).fail(function (xhr) {
            console.error('Failed to load media content:', xhr);
            $(contentSelector).html('<p class="text-center text-danger">Не удалось загрузить медиафайлы.</p>');
            toastr.error('Не удалось загрузить галерею медиа.');
        });
    }
}

// Обработка кликов по элементам медиа
export function addMediaItemClickHandlers(containerSelector) {
    $(containerSelector).off('click', '.media-item');
    $(containerSelector).on('click', '.media-item', function (e) {
        if ($(e.target).hasClass('delete-media-btn') || $(e.target).closest('.delete-media-btn').length) {
            return;
        }
        const $item = $(this);
        $item.toggleClass('selected');
    });
}

// Восстановление выделения в модальном окне
export function restoreSelectedMediaInModal() {
    const selectedDbIdsString = $('#selectedMediaIds').val();
    const selectedDbIds = selectedDbIdsString ? selectedDbIdsString.split(',').map(id => parseInt(id)).filter(id => !isNaN(id)) : [];

    $('#mediaItemsList .media-item').each(function () {
        const mediaId = $(this).data('media-id');
        $(this).toggleClass('selected', selectedDbIds.includes(mediaId));
    });
}

// Обработка прикрепления выбранных медиа
export function attachSelectedMedia(buttonSelector, modalSelector) {
    $(buttonSelector).on('click', function () {
        const selectedIds = $('#mediaItemsList .media-item.selected').map(function () {
            return $(this).data('media-id');
        }).get();

        const currentFilepondTempIds = $('#selectedMediaPreview .media-preview-item').filter(function () {
            const id = $(this).data('media-id');
            return typeof id === 'string' && id.startsWith('filepond-tmp/');
        }).map(function () {
            return $(this).data('media-id');
        }).get();

        const newMediaOrder = [...currentFilepondTempIds, ...selectedIds];
        $('#selectedMediaIds').val(selectedIds.join(','));
        $('#mediaOrder').val(newMediaOrder.join(','));
        updateSelectedMediaPreview(selectedIds);
        $(modalSelector).modal('hide');
    });
}

// Обновление превью выбранных медиа
export function updateSelectedMediaPreview(selectedDbIds) {
    const $previewContainer = $('#selectedMediaPreview');
    const currentFilepondItems = $previewContainer.children('.media-preview-item').filter(function () {
        const id = $(this).data('media-id');
        return typeof id === 'string' && id.startsWith('filepond-tmp/');
    });

    let filepondHtml = '';
    currentFilepondItems.each(function () {
        filepondHtml += this.outerHTML;
    });

    $previewContainer.empty();
    $previewContainer.append(filepondHtml);

    if (selectedDbIds.length > 0) {
        $.get(window.appConfig.routes.mediaGetByIds, { ids: selectedDbIds }, function (mediaItems) {
            const mediaOrderArray = $('#mediaOrder').val().split(',').filter(id => id !== '');
            mediaItems.sort(function (a, b) {
                const indexA = mediaOrderArray.indexOf(String(a.id));
                const indexB = mediaOrderArray.indexOf(String(b.id));
                if (indexA === -1 && indexB === -1) return 0;
                if (indexA === -1) return 1;
                if (indexB === -1) return -1;
                return indexA - indexB;
            });

            mediaItems.forEach(function (media) {
                if (media && media.id && $previewContainer.find(`.media-preview-item[data-media-id="${media.id}"]`).length === 0) {
                    const imageUrl = media.url_thumb || media.url;
                    const isImage = media.mime_type && media.mime_type.startsWith('image/');
                    $previewContainer.append(`
                        <div class="media-preview-item col-auto p-0 mr-2 mb-2" data-media-id="${media.id}">
                            <button type="button" class="remove-btn" data-media-id="${media.id}">×</button>
                            <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 80px; height: 80px; overflow: hidden;">
                                ${isImage ? `<img src="${imageUrl}" class="img-thumbnail rounded" alt="${media.file_name}">` : `<i class="fas fa-file fa-3x text-muted"></i>`}
                            </div>
                            <small class="d-block text-truncate mt-1" title="${media.file_name}">${media.file_name}</small>
                        </div>
                    `);
                }
            });

            updateMediaOrder($previewContainer);

            $previewContainer.off('click', '.remove-btn');
            $previewContainer.on('click', '.remove-btn', function () {
                const mediaIdToRemove = $(this).data('media-id');
                $(this).closest('.media-preview-item').remove();
                updateMediaOrder($previewContainer);

                if (typeof mediaIdToRemove === 'number' && !isNaN(mediaIdToRemove)) {
                    const currentSelectedDbIds = $('#selectedMediaIds').val().split(',').map(id => parseInt(id)).filter(id => !isNaN(id));
                    const updatedSelectedDbIds = currentSelectedDbIds.filter(id => id !== mediaIdToRemove);
                    $('#selectedMediaIds').val(updatedSelectedDbIds.join(','));
                }
            });
        });
    } else {
        updateMediaOrder($previewContainer);
    }
}

// Обновление порядка медиа
export function updateMediaOrder($previewContainer) {
    const orderedIds = $previewContainer.find('.media-preview-item').map(function () {
        return $(this).data('media-id');
    }).get();
    $('#mediaOrder').val(orderedIds.join(','));

    const sortable = $previewContainer[0].sortableInstance;
    if (sortable) {
        sortable.sort(orderedIds);
    }
}

// Инициализация SortableJS для основной формы
export function initMainSortable(containerSelector) {
    const previewContainer = document.querySelector(containerSelector);
    if (previewContainer && !previewContainer.sortableInstance) {
        const sortable = Sortable.create(previewContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            draggable: '.media-preview-item',
            onEnd: () => updateMediaOrder($(containerSelector))
        });
        previewContainer.sortableInstance = sortable;
    }
}

// Инициализация SortableJS для модального окна
export function initModalSortable(containerSelector) {
    $(document).on('shown.bs.modal', '#mediaLibraryModal', function () {
        const modalContainer = document.querySelector(containerSelector);
        if (modalContainer && !modalContainer.sortableInstance) {
            const sortable = Sortable.create(modalContainer, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                draggable: '.media-item'
            });
            modalContainer.sortableInstance = sortable;
        }
    });
}

// Инициализация обработки удаления медиа
export function initMediaDeletion(containerSelector) {
    $(containerSelector).off('click', '.delete-media-btn');
    $(containerSelector).on('click', '.delete-media-btn', function (e) {
        e.stopPropagation();
        const mediaId = $(this).data('media-id');

        if (confirm('Вы уверены, что хотите полностью удалить этот медиафайл? Это действие необратимо и удалит файл из всех связанных сущностей.')) {
            $.ajax({
                url: `${window.appConfig.routes.mediaIndex}/${mediaId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success: function (response) {
                    $(`.media-item[data-media-id="${mediaId}"]`).remove();

                    const currentSelectedIdsString = $('#selectedMediaIds').val();
                    const currentSelectedIds = currentSelectedIdsString ? currentSelectedIdsString.split(',').map(id => parseInt(id)).filter(id => !isNaN(id)) : [];
                    const updatedSelectedIds = currentSelectedIds.filter(id => id !== mediaId);
                    $('#selectedMediaIds').val(updatedSelectedIds.join(','));

                    updateSelectedMediaPreview(updatedSelectedIds);

                    if ($('#mediaItemsList .media-item').length === 0) {
                        $('#mediaItemsList').html('<div class="col-12"><p class="text-center">Медиафайлы не найдены.</p></div>');
                    }

                    toastr.success(response.message || 'Медиафайл удален.');
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Ошибка при удалении медиафайла.';
                    toastr.error(errorMessage);
                }
            });
        }
    });
}
