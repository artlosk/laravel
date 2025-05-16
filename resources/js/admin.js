// resources/js/admin.js
import {
    initializeFilePond,
    loadMediaLibraryModal,
    attachSelectedMedia,
    updateSelectedMediaPreview,
    initMainSortable,
    initModalSortable
} from './mediaUtils.js';

document.addEventListener('DOMContentLoaded', () => {
    console.log('admin.js loaded, jQuery:', typeof $ !== 'undefined' ? $.fn.jquery : 'undefined');
    console.log('Bootstrap modal:', $.fn.modal !== 'undefined' ? 'available' : 'unavailable');
    console.log('window.appConfig:', window.appConfig);

    const form = document.querySelector('#postForm');
    if (!form) {
        console.warn('Form #postForm not found');
        return;
    }

    // Инициализация FilePond
    initializeFilePond('input[type="file"].filepond');

    // Инициализация модального окна
    console.log('Calling loadMediaLibraryModal');
    loadMediaLibraryModal('#mediaLibraryModal', '#mediaItemsList');

    // Обработка прикрепления медиа
    attachSelectedMedia('#attachSelectedMedia', '#mediaLibraryModal');

    // Инициализация SortableJS для основной формы
    initMainSortable('#selectedMediaPreview');

    // Инициализация SortableJS для модального окна
    initModalSortable('#mediaItemsList');

    // Инициализация превью для редактирования поста
    const selectedMediaIds = document.querySelector('#selectedMediaIds')?.value;
    if (selectedMediaIds) {
        const initialMediaIds = selectedMediaIds.split(',').map(id => parseInt(id)).filter(id => !isNaN(id));
        if (initialMediaIds.length > 0) {
            updateSelectedMediaPreview(initialMediaIds);
        }
    }
});
