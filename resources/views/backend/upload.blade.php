

@section('content')
    <div class="container">
        <h1>Загрузить изображение</h1>

        <!-- Форма загрузки с Filepond -->
        <form id="filepond-form" method="POST" action="{{ url('admin/upload-media') }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="filepond" id="filepond" accept="image/*">
            <button type="submit">Загрузить</button>
        </form>

        <div id="media-preview"></div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/filepond/dist/filepond.min.js"></script>
    <script>
        // Инициализация Filepond
        const inputElement = document.querySelector('input[id="filepond"]');
        const pond = FilePond.create(inputElement);

        // Отправка данных на сервер
        pond.on('processfile', (error, file) => {
            if (error) {
                console.log('Error during upload', error);
                return;
            }

            // Отображение изображения после загрузки
            const mediaPreview = document.getElementById('media-preview');
            const imgElement = document.createElement('img');
            imgElement.src = file.serverId; // URL медиафайла
            imgElement.alt = 'Uploaded image';
            mediaPreview.appendChild(imgElement);
        });
    </script>
@endsection
