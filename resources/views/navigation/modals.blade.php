<!-- Модальное окно для прибытия на точку -->
<div id="arrival-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[1100] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full max-h-[90vh] overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Прибытие на точку</h3>
                        <p class="text-sm text-gray-600" id="checkpoint-title"></p>
                    </div>
                </div>
                <button onclick="closeArrivalModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="arrival-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="checkpoint-id" name="checkpoint_id">
                
                <div class="mb-4">
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                        Комментарий <span class="text-gray-500">(опционально)</span>
                    </label>
                    <textarea id="comment" name="comment" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                              placeholder="Поделитесь впечатлениями, что интересного увидели..."></textarea>
                </div>
                
                <div class="mb-6">
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">
                        Добавить фото
                    </label>
                    <div class="relative">
                        <input type="file" id="photo" name="photo" accept="image/*" 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                               onchange="previewPhoto(event)">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-green-400 transition-colors">
                            <i class="fas fa-camera text-gray-400 text-3xl mb-3"></i>
                            <p class="text-gray-600 mb-1">Нажмите для загрузки фото</p>
                            <p class="text-sm text-gray-500">JPG, PNG до 5MB</p>
                        </div>
                    </div>
                    <div id="photo-preview" class="mt-3 hidden">
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-image text-gray-400 mr-3"></i>
                                <div>
                                    <div class="font-medium text-gray-800 text-sm" id="photo-name"></div>
                                    <div class="text-xs text-gray-500" id="photo-size"></div>
                                </div>
                            </div>
                            <button type="button" onclick="removePhotoPreview()" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Рейтинг точки -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Оценка точки</label>
                    <div class="flex items-center space-x-1" id="rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <input type="radio" id="rating_{{ $i }}" name="rating" value="{{ $i }}" class="hidden">
                            <label for="rating_{{ $i }}" class="cursor-pointer text-2xl rating-star">
                                <i class="far fa-star text-gray-300 hover:text-yellow-400"></i>
                            </label>
                        @endfor
                    </div>
                </div>
            </form>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <button type="button" onclick="closeArrivalModal()"
                    class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                Отмена
            </button>
            <button type="submit" form="arrival-form"
                    class="px-5 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-bold hover:from-green-600 hover:to-emerald-700 transition-colors flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                Подтвердить прибытие
            </button>
        </div>
    </div>
</div>

<!-- Модальное окно для выполнения задания -->
<div id="task-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[1100] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-auto">
        <div class="p-6">
            <button onclick="closeTaskModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <div id="task-content">
                <!-- Контент будет загружен динамически -->
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно информации о точке -->
<div id="checkpoint-info-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[1100] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800" id="info-title"></h3>
                    <div class="flex items-center mt-2">
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium mr-3"
                              id="info-type"></span>
                        <span class="text-gray-600" id="info-distance"></span>
                    </div>
                </div>
                <button onclick="closeCheckpointInfoModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="mb-6">
                <div class="aspect-video bg-gray-100 rounded-xl mb-4 overflow-hidden" id="info-photo-container">
                    <!-- Фото будет загружено динамически -->
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-image text-gray-400 text-4xl"></i>
                    </div>
                </div>
                
                <p class="text-gray-700 mb-6" id="info-description"></p>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-600 mb-1">Координаты</div>
                        <div class="font-mono text-gray-800 text-sm" id="info-coordinates"></div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-600 mb-1">Порядковый номер</div>
                        <div class="text-2xl font-bold text-gray-800" id="info-order"></div>
                    </div>
                </div>
                
                <!-- Квесты связанные с точкой -->
                <div id="quests-container" class="hidden">
                    <h4 class="font-bold text-gray-800 mb-3">Квесты на этой точке</h4>
                    <div id="quests-list" class="space-y-3">
                        <!-- Квесты будут загружены динамически -->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between">
            <div>
                <button onclick="skipCheckpointFromInfo()" class="text-gray-600 hover:text-gray-800 font-medium">
                    <i class="fas fa-forward mr-2"></i> Пропустить точку
                </button>
            </div>
            <button onclick="arriveFromInfo()" 
                    class="px-5 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-bold hover:from-green-600 hover:to-emerald-700 transition-colors">
                <i class="fas fa-check-circle mr-2"></i> Прибыл на точку
            </button>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения пропуска -->
<div id="skip-confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[1100] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        <div class="p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
            </div>
            
            <h3 class="text-2xl font-bold text-gray-800 mb-3">Пропустить точку?</h3>
            <p class="text-gray-600 mb-6">
                Вы уверены, что хотите пропустить эту контрольную точку? 
                Это может повлиять на прогресс квестов и получение достижений.
            </p>
            
            <div class="flex justify-center gap-4">
                <button onclick="closeSkipModal()"
                        class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                    Отмена
                </button>
                <button onclick="confirmSkip()" id="confirm-skip-btn"
                        class="px-6 py-3 bg-gradient-to-r from-yellow-500 to-orange-600 text-white rounded-lg font-bold hover:from-yellow-600 hover:to-orange-700 transition-colors">
                    Да, пропустить
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно завершения маршрута -->
<div id="complete-route-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[1100] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full">
        <div class="p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 rounded-full bg-gradient-to-r from-green-100 to-emerald-200 flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-flag-checkered text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-3xl font-bold text-gray-800 mb-3">Завершить маршрут?</h3>
                <p class="text-gray-600">Вы прошли {{ $completedCheckpoints }} из {{ $totalCheckpoints }} точек</p>
            </div>
            
            <div class="bg-gray-50 rounded-xl p-6 mb-8">
                <h4 class="font-bold text-gray-800 mb-4 text-lg">Итоги маршрута</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-white rounded-lg">
                        <div class="text-2xl font-bold text-gray-800 mb-1">{{ $completedCheckpoints }}</div>
                        <div class="text-sm text-gray-600">Точек пройдено</div>
                    </div>
                    <div class="text-center p-4 bg-white rounded-lg">
                        <div class="text-2xl font-bold text-gray-800 mb-1">{{ $session->duration_formatted ?? '00:00' }}</div>
                        <div class="text-sm text-gray-600">Время в пути</div>
                    </div>
                    <div class="text-center p-4 bg-white rounded-lg">
                        <div class="text-2xl font-bold text-gray-800 mb-1">{{ $earnedXp ?? 0 }}</div>
                        <div class="text-sm text-gray-600">XP заработано</div>
                    </div>
                    <div class="text-center p-4 bg-white rounded-lg">
                        <div class="text-2xl font-bold text-gray-800 mb-1">{{ $activeQuests->count() ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Активных квестов</div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-4">
                <button onclick="closeCompleteModal()"
                        class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                    Продолжить
                </button>
                <form action="{{ route('routes.navigation.complete', $session) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-bold hover:from-green-600 hover:to-emerald-700 transition-colors">
                        <i class="fas fa-flag-checkered mr-2"></i> Завершить маршрут
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для просмотра фото -->
<div id="photo-viewer-modal" class="hidden fixed inset-0 bg-black z-[1100] flex items-center justify-center">
    <div class="relative w-full h-full">
        <button onclick="closePhotoViewer()" class="absolute top-4 right-4 text-white text-3xl z-10 hover:text-gray-300">
            <i class="fas fa-times"></i>
        </button>
        <div class="absolute top-4 left-4 text-white z-10">
            <div class="font-medium" id="photo-title"></div>
            <div class="text-sm text-gray-300" id="photo-date"></div>
        </div>
        <img id="fullscreen-photo" class="max-w-full max-h-full object-contain mx-auto" src="" alt="">
    </div>
</div>

<!-- Модальное окно для QR-кода -->
<div id="qrcode-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[1100] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        <div class="p-8 text-center">
            <div class="w-32 h-32 mx-auto mb-6 bg-gray-100 rounded-lg flex items-center justify-center" id="qrcode-container">
                <!-- QR-код будет сгенерирован здесь -->
                <i class="fas fa-qrcode text-gray-400 text-4xl"></i>
            </div>
            
            <h3 class="text-2xl font-bold text-gray-800 mb-3">QR-код точки</h3>
            <p class="text-gray-600 mb-6">Отсканируйте QR-код для подтверждения прибытия на точку</p>
            
            <div class="text-left bg-gray-50 rounded-lg p-4 mb-6">
                <div class="text-sm text-gray-600 mb-1">Точка</div>
                <div class="font-medium text-gray-800" id="qrcode-title"></div>
            </div>
            
            <button onclick="closeQRCodeModal()"
                    class="w-full px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                Закрыть
            </button>
        </div>
    </div>
</div>

<script>
// Переменные для управления модальными окнами
let currentCheckpointIdForSkip = null;

// Показать информацию о точке
function showCheckpointInfo(checkpointId) {
    // Здесь можно загрузить данные о точке через AJAX
    // Для примера покажем заглушку
    document.getElementById('info-title').textContent = 'Информация о точке';
    document.getElementById('info-type').textContent = 'Контрольная точка';
    document.getElementById('info-distance').textContent = '~ 150 м от вас';
    document.getElementById('info-description').textContent = 'Подробное описание контрольной точки...';
    document.getElementById('info-coordinates').textContent = '55.7558, 37.6173';
    document.getElementById('info-order').textContent = '#1';
    
    // Скрываем контейнер квестов если их нет
    document.getElementById('quests-container').classList.add('hidden');
    
    document.getElementById('checkpoint-info-modal').classList.remove('hidden');
}

// Закрыть информацию о точке
function closeCheckpointInfoModal() {
    document.getElementById('checkpoint-info-modal').classList.add('hidden');
}

// Пропустить точку из информационного окна
function skipCheckpointFromInfo() {
    const checkpointId = currentCheckpointIdForSkip || {{ $currentCheckpoint->id ?? 0 }};
    closeCheckpointInfoModal();
    showSkipModal(checkpointId);
}

// Прибыть на точку из информационного окна
function arriveFromInfo() {
    const checkpointId = currentCheckpointIdForSkip || {{ $currentCheckpoint->id ?? 0 }};
    closeCheckpointInfoModal();
    arriveAtCheckpoint(checkpointId);
}

// Показать модальное окно пропуска
function showSkipModal(checkpointId) {
    currentCheckpointIdForSkip = checkpointId;
    document.getElementById('skip-confirm-modal').classList.remove('hidden');
}

// Закрыть модальное окно пропуска
function closeSkipModal() {
    document.getElementById('skip-confirm-modal').classList.add('hidden');
    currentCheckpointIdForSkip = null;
}

// Подтвердить пропуск
function confirmSkip() {
    if (currentCheckpointIdForSkip) {
        skipCheckpoint(currentCheckpointIdForSkip);
        closeSkipModal();
    }
}

// Показать модальное окно завершения маршрута
function showCompleteModal() {
    document.getElementById('complete-route-modal').classList.remove('hidden');
}

// Закрыть модальное окно завершения маршрута
function closeCompleteModal() {
    document.getElementById('complete-route-modal').classList.add('hidden');
}

// Показать модальное окно прибытия
function showArrivalModal(checkpointId) {
    // Устанавливаем ID точки
    document.getElementById('checkpoint-id').value = checkpointId;
    
    // Загружаем информацию о точке
    fetch(`/api/checkpoints/${checkpointId}/info`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('checkpoint-title').textContent = data.checkpoint.title;
                document.getElementById('checkpoint-description').textContent = data.checkpoint.description || 'Нет описания';
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки информации о точке:', error);
        });
    
    document.getElementById('arrival-modal').classList.remove('hidden');
}

// Закрыть модальное окно прибытия
function closeArrivalModal() {
    document.getElementById('arrival-modal').classList.add('hidden');
    resetArrivalForm();
}

// Сбросить форму прибытия
function resetArrivalForm() {
    document.getElementById('arrival-form').reset();
    document.getElementById('photo-preview').classList.add('hidden');
    
    // Сбрасываем звезды рейтинга
    document.querySelectorAll('.rating-star i').forEach(star => {
        star.className = 'far fa-star text-gray-300';
    });
}

// Предпросмотр фото
function previewPhoto(event) {
    const file = event.target.files[0];
    if (file) {
        const preview = document.getElementById('photo-preview');
        const name = document.getElementById('photo-name');
        const size = document.getElementById('photo-size');
        
        // Показываем информацию о файле
        name.textContent = file.name;
        size.textContent = formatFileSize(file.size);
        preview.classList.remove('hidden');
        
        // Показываем превью изображения
        const reader = new FileReader();
        reader.onload = function(e) {
            // Можно добавить миниатюру изображения
            console.log('Фото загружено:', e.target.result.substring(0, 100));
        };
        reader.readAsDataURL(file);
    }
}

// Удалить превью фото
function removePhotoPreview() {
    document.getElementById('photo').value = '';
    document.getElementById('photo-preview').classList.add('hidden');
}

// Форматирование размера файла
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Отправка формы прибытия
document.getElementById('arrival-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const checkpointId = document.getElementById('checkpoint-id').value;
    
    fetch(`/api/checkpoints/${checkpointId}/arrive`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Успех', 'Точка успешно отмечена!', 'success');
            closeArrivalModal();
            location.reload();
        } else {
            showNotification('Ошибка', data.message || 'Не удалось отметить прибытие', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка', 'Ошибка при отправке запроса', 'error');
    });
});

// Показать результат выполнения задания
function showTaskResult(task) {
    const modalContent = `
        <div class="text-center">
            <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-green-600 text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">Задание выполнено!</h3>
            <p class="text-gray-600 mb-2">${task.title || 'Задание'}</p>
            <div class="bg-gray-50 rounded-xl p-6 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-gray-600">Получено очков:</span>
                    <span class="font-bold text-green-600 text-xl">+${task.points || 0}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Статус:</span>
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                        <i class="fas fa-check mr-1"></i> Выполнено
                    </span>
                </div>
            </div>
            <button onclick="closeTaskModal()" 
                    class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg font-bold hover:from-blue-600 hover:to-indigo-700 transition-colors">
                Продолжить маршрут
            </button>
        </div>
    `;
    
    document.getElementById('task-content').innerHTML = modalContent;
    document.getElementById('task-modal').classList.remove('hidden');
}

// Закрыть модальное окно задания
function closeTaskModal() {
    document.getElementById('task-modal').classList.add('hidden');
    // Обновляем прогресс на странице
    setTimeout(() => {
        updateQuestProgress();
    }, 500);
}

// Показать фото в полноэкранном режиме
function showFullscreenPhoto(photoUrl, title = '', date = '') {
    const img = document.getElementById('fullscreen-photo');
    const photoTitle = document.getElementById('photo-title');
    const photoDate = document.getElementById('photo-date');
    
    img.src = photoUrl;
    photoTitle.textContent = title;
    photoDate.textContent = date;
    
    document.getElementById('photo-viewer-modal').classList.remove('hidden');
}

// Закрыть просмотр фото
function closePhotoViewer() {
    document.getElementById('photo-viewer-modal').classList.add('hidden');
}

// Показать QR-код
function showQRCode(checkpointId, title) {
    document.getElementById('qrcode-title').textContent = title || 'Контрольная точка';
    
    // Здесь можно сгенерировать QR-код с помощью библиотеки
    // Например: new QRCode(document.getElementById("qrcode-container"), "https://example.com/checkpoint/" + checkpointId);
    
    document.getElementById('qrcode-modal').classList.remove('hidden');
}

// Закрыть QR-код
function closeQRCodeModal() {
    document.getElementById('qrcode-modal').classList.add('hidden');
}

// Обработка нажатия на звезды рейтинга
document.querySelectorAll('.rating-star').forEach((star, index) => {
    star.addEventListener('click', function() {
        const rating = index + 1;
        
        // Обновляем отображение звезд
        document.querySelectorAll('.rating-star i').forEach((s, i) => {
            if (i < rating) {
                s.className = 'fas fa-star text-yellow-400';
            } else {
                s.className = 'far fa-star text-gray-300';
            }
        });
        
        // Устанавливаем значение в скрытом поле
        document.querySelector(`#rating_${rating}`).checked = true;
    });
});

// Обработка нажатия Escape для закрытия модальных окон
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('[class*="modal"]:not(.hidden)');
        if (openModals.length > 0) {
            // Закрываем последнее открытое модальное окно
            openModals[openModals.length - 1].classList.add('hidden');
        }
    }
});

// Закрытие модальных окон при клике на фон
document.querySelectorAll('[class*="modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this && !this.classList.contains('hidden')) {
            this.classList.add('hidden');
        }
    });


    function showCompleteRouteModal() {
    document.getElementById('complete-route-modal').classList.remove('hidden');
}

function showQRCodeForCheckpoint(checkpointId, title) {
    // Получаем данные о точке
    fetch(`/api/checkpoints/${checkpointId}/info`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showQRCode(checkpointId, data.checkpoint.title);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showNotification('Ошибка', 'Не удалось загрузить информацию о точке', 'error');
        });
}

// Обновление прогресса после выполнения задания
function updateQuestProgress() {
    // Перезагружаем страницу для обновления данных
    // Можно заменить на AJAX запрос для обновления только квестов
    location.reload();
}

// Инициализация обработчиков для звезд рейтинга
function initRatingStars() {
    document.querySelectorAll('.rating-star').forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = index + 1;
            
            // Обновляем все звезды
            document.querySelectorAll('.rating-star i').forEach((s, i) => {
                if (i < rating) {
                    s.className = 'fas fa-star text-yellow-400';
                } else {
                    s.className = 'far fa-star text-gray-300';
                }
            });
            
            // Находим соответствующий input и устанавливаем значение
            const input = document.querySelector(`#rating_${rating}`);
            if (input) {
                input.checked = true;
            }
        });
    });
}

// Добавьте вызов инициализации в DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    initRatingStars();
});
</script>