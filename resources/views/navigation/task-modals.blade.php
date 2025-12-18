<!-- Шаблоны для разных типов заданий -->
<div id="task-templates" style="display: none;">
    
    <!-- Текстовое задание -->
    <template id="task-template-text">
        <div class="task-modal task-type-text">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                        <i class="fas fa-font text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm font-medium text-gray-700 mb-2">Вопрос:</div>
                    <div class="text-gray-800" data-bind="content.question"></div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ваш ответ:</label>
                    <textarea id="task-text-answer" rows="4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Введите ваш ответ..." 
                        data-maxlength="500"></textarea>
                    <div class="text-xs text-gray-500 mt-1 text-right">
                        <span id="text-char-count">0</span>/<span data-bind="content.max_length">500</span> символов
                    </div>
                </div>
                
                <div class="mb-4 hints-section" style="display: none;">
                    <button onclick="requestHint()" 
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i> Получить подсказку
                        <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                            <span data-bind="hints_available">0</span> доступно
                        </span>
                    </button>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitTextTask()"
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-bold">
                        <i class="fas fa-paper-plane mr-2"></i>Отправить ответ
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Задание с фото -->
    <template id="task-template-image">
        <div class="task-modal task-type-image">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                        <i class="fas fa-camera text-purple-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm font-medium text-gray-700 mb-2">Задание:</div>
                    <div class="text-gray-800" data-bind="content.description"></div>
                    
                    <div class="mt-3 required-elements" style="display: none;">
                        <div class="text-sm font-medium text-gray-700 mb-1">На фото должны быть:</div>
                        <ul class="list-disc list-inside text-sm text-gray-600 required-elements-list">
                        </ul>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Выберите фото:</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-purple-400 transition-colors cursor-pointer"
                         onclick="document.getElementById('task-photo-input').click()">
                        <input type="file" id="task-photo-input" accept="image/*" capture="environment" 
                               class="hidden" onchange="previewTaskPhoto(this)">
                        <div id="photo-upload-area">
                            <i class="fas fa-camera text-gray-400 text-3xl mb-3"></i>
                            <p class="text-gray-600 mb-1">Нажмите для выбора фото</p>
                            <p class="text-sm text-gray-500">или сделайте снимок (до 10MB)</p>
                        </div>
                    </div>
                    <div id="photo-preview-container" class="mt-3 hidden">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <i class="fas fa-image text-gray-400 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-800 text-sm" id="photo-filename"></div>
                                        <div class="text-xs text-gray-500" id="photo-filesize"></div>
                                    </div>
                                </div>
                                <button type="button" onclick="removePhotoPreview()" 
                                        class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <img id="photo-preview-image" class="w-full h-48 object-cover rounded-lg">
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Описание фото:</label>
                    <textarea id="photo-description" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        placeholder="Опишите, что на фото..."></textarea>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitPhotoTask()"
                            class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-bold">
                        <i class="fas fa-upload mr-2"></i>Загрузить фото
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Викторина -->
    <template id="task-template-quiz">
        <div class="task-modal task-type-quiz">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                        <i class="fas fa-question-circle text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-lg font-medium text-gray-800 mb-3" data-bind="content.question"></div>
                    
                    <div id="quiz-options" class="space-y-2">
                        <!-- Options will be inserted here -->
                    </div>
                </div>
                
                <div class="mb-4 hints-section" style="display: none;">
                    <button onclick="requestHint()" 
                            class="text-sm text-green-600 hover:text-green-800 font-medium flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i> Получить подсказку
                        <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                            <span data-bind="hints_available">0</span> доступно
                        </span>
                    </button>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitQuizTask()"
                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-bold">
                        <i class="fas fa-check mr-2"></i>Ответить
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Задание с кодом -->
    <template id="task-template-code">
        <div class="task-modal task-type-code">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center mr-3">
                        <i class="fas fa-code text-orange-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm font-medium text-gray-700 mb-2">Задание:</div>
                    <div class="text-gray-800 mb-3" data-bind="content.description"></div>
                    
                    <div class="bg-gray-900 text-gray-100 rounded-lg p-4 font-mono text-sm overflow-x-auto">
                        <pre data-bind="content.code"></pre>
                    </div>
                    
                    <div class="mt-3 expected-output" style="display: none;">
                        <div class="text-sm font-medium text-gray-700 mb-1">Ожидаемый результат:</div>
                        <div class="bg-gray-800 text-green-400 rounded-lg p-3 font-mono text-sm" data-bind="content.expected_output">
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ваш код:</label>
                    <div class="flex items-center mb-2">
                        <span class="text-sm text-gray-600 mr-3">Язык:</span>
                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm" data-bind="content.language">
                            javascript
                        </span>
                    </div>
                    <textarea id="code-answer" rows="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Напишите ваш код здесь..."></textarea>
                </div>
                
                <div class="mb-4 hints-section" style="display: none;">
                    <button onclick="requestHint()" 
                            class="text-sm text-orange-600 hover:text-orange-800 font-medium flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i> Получить подсказку
                        <span class="ml-2 text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded">
                            <span data-bind="hints_available">0</span> доступно
                        </span>
                    </button>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitCodeTask()"
                            class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-bold">
                        <i class="fas fa-play mr-2"></i>Запустить код
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Шифр -->
    <template id="task-template-cipher">
        <div class="task-modal task-type-cipher">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-3">
                        <i class="fas fa-key text-yellow-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm font-medium text-gray-700 mb-2">Зашифрованное сообщение:</div>
                    <div class="bg-gray-800 text-yellow-300 rounded-lg p-4 font-mono text-center text-lg mb-3" data-bind="content.cipher_text">
                    </div>
                    
                    <div class="text-sm text-gray-600 mb-2">Тип шифра: 
                        <span class="font-medium cipher-type-label">Шифр Цезаря</span>
                    </div>
                    
                    <div class="mt-3 hint-container" style="display: none;">
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="text-sm font-medium text-blue-700 mb-1">Подсказка:</div>
                            <div class="text-sm text-blue-600" data-bind="content.hint"></div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Расшифрованный текст:</label>
                    <textarea id="cipher-answer" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                        placeholder="Введите расшифрованный текст..."></textarea>
                </div>
                
                <div class="mb-4 hints-section" style="display: none;">
                    <button onclick="requestHint()" 
                            class="text-sm text-yellow-600 hover:text-yellow-800 font-medium flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i> Получить подсказку
                        <span class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                            <span data-bind="hints_available">0</span> доступно
                        </span>
                    </button>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitCipherTask()"
                            class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-bold">
                        <i class="fas fa-unlock mr-2"></i>Расшифровать
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Головоломка -->
    <template id="task-template-puzzle">
        <div class="task-modal task-type-puzzle">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center mr-3">
                        <i class="fas fa-puzzle-piece text-pink-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-gray-800 mb-3" data-bind="content.puzzle"></div>
                    
                    <div id="puzzle-container" class="mt-4">
                        <div class="grid grid-cols-3 gap-2" id="puzzle-grid">
                            <!-- Puzzle pieces will be inserted here -->
                        </div>
                    </div>
                </div>
                
                <div class="mb-4 hints-section" style="display: none;">
                    <button onclick="requestHint()" 
                            class="text-sm text-pink-600 hover:text-pink-800 font-medium flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i> Получить подсказку
                        <span class="ml-2 text-xs bg-pink-100 text-pink-800 px-2 py-1 rounded">
                            <span data-bind="hints_available">0</span> доступно
                        </span>
                    </button>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitPuzzleTask()"
                            class="px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg font-bold">
                        <i class="fas fa-check-double mr-2"></i>Проверить решение
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Локация -->
    <template id="task-template-location">
        <div class="task-modal task-type-location">
            <div class="task-header">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                        <i class="fas fa-map-marker-alt text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" data-bind="title"></h3>
                        <p class="text-sm text-gray-600" data-bind="description"></p>
                    </div>
                </div>
            </div>
            
            <div class="task-content mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-gray-800 mb-3" data-bind="content.description"></div>
                    
                    <div class="flex items-center text-sm text-gray-600 mb-2">
                        <i class="fas fa-location-arrow mr-2"></i>
                        <span>Радиус поиска: <span data-bind="content.radius">100</span> метров</span>
                    </div>
                    
                    <div class="mt-3 coordinates-container" style="display: none;">
                        <button onclick="showLocationOnMap()"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                            <i class="fas fa-map mr-2"></i> Показать на карте
                        </button>
                    </div>
                    
                    <div class="mt-4 text-center qr-container" style="display: none;">
                        <div class="text-sm font-medium text-gray-700 mb-2">Или отсканируйте QR-код:</div>
                        <div class="bg-white p-4 rounded-lg inline-block">
                            <img src="" alt="QR Code" class="w-32 h-32 qr-image">
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="text-sm font-medium text-gray-700 mb-2">Ваше текущее местоположение:</div>
                    <div id="location-info" class="text-sm text-gray-600">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-sync-alt animate-spin mr-2"></i>
                            Определяем местоположение...
                        </div>
                    </div>
                    <button onclick="getCurrentLocation()"
                            class="mt-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium flex items-center">
                        <i class="fas fa-location-crosshairs mr-2"></i> Обновить местоположение
                    </button>
                </div>
            </div>
            
            <div class="task-footer">
                <div class="flex justify-end gap-2">
                    <button onclick="closeTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Отмена
                    </button>
                    <button onclick="submitLocationTask()"
                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold"
                            disabled id="submit-location-btn">
                        <i class="fas fa-check-circle mr-2"></i>Я на месте!
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Шаблон ошибки -->
    <template id="task-template-error">
        <div class="task-modal task-type-error">
            <div class="text-center py-8">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Ошибка загрузки задания</h3>
                <p class="text-gray-600 mb-6" id="error-message">Не удалось загрузить задание. Проверьте соединение с интернетом.</p>
                <div class="space-y-3">
                    <button onclick="retryLoadTask()"
                            class="w-full px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium">
                        <i class="fas fa-redo mr-2"></i> Повторить попытку
                    </button>
                    <button onclick="closeTaskModal()"
                            class="w-full px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">
                        Закрыть
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <!-- Шаблон результата -->
    <template id="task-template-result">
        <div class="task-modal task-type-result">
            <div class="text-center py-8">
                <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Задание выполнено!</h3>
                <p class="text-gray-600 mb-2" id="result-title"></p>
                
                <div class="bg-gray-50 rounded-xl p-6 mb-8 max-w-sm mx-auto">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Награда:</span>
                            <span class="font-bold text-green-600" id="result-points"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Получено XP:</span>
                            <span class="font-bold text-blue-600" id="result-xp"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Статус:</span>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                <i class="fas fa-check mr-1"></i> Выполнено
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="text-sm text-gray-500 mb-8" id="result-message"></div>
                
                <button onclick="closeTaskModalAndRefresh()"
                        class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg font-bold hover:from-blue-600 hover:to-indigo-700">
                    Продолжить маршрут
                </button>
            </div>
        </div>
    </template>
</div>

<style>
.task-modal {
    max-height: 80vh;
    overflow-y: auto;
}

.task-type-text textarea {
    font-family: inherit;
}

.task-type-code textarea {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    tab-size: 4;
}

.task-type-cipher .cipher-text {
    font-family: 'Courier New', monospace;
    letter-spacing: 2px;
}

.puzzle-piece {
    user-select: none;
    touch-action: none;
    cursor: move;
}

.puzzle-piece.dragging {
    opacity: 0.5;
    transform: scale(1.05);
}

#photo-preview-image {
    max-height: 300px;
    object-fit: contain;
}

.task-type-location #location-info {
    font-family: monospace;
}

@media (max-width: 640px) {
    .task-modal {
        max-height: 90vh;
        margin: 0;
        border-radius: 0;
    }
    
    .puzzle-piece {
        padding: 10px;
        font-size: 0.875rem;
    }
}
</style>

<script>
// Глобальные переменные для заданий
let currentTask = null;
let currentTaskId = null;

// Простой шаблонизатор с использованием data-bind
function renderTemplate(templateId, data) {
    const template = document.getElementById(templateId);
    if (!template) return '';
    
    const clone = template.content.cloneNode(true);
    
    // Заполняем данные через data-bind атрибуты
    const elements = clone.querySelectorAll('[data-bind]');
    elements.forEach(element => {
        const bindPath = element.getAttribute('data-bind');
        const value = getValueByPath(data, bindPath);
        
        if (value !== undefined && value !== null) {
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.value = value;
            } else if (element.tagName === 'IMG') {
                element.src = value;
            } else {
                element.textContent = value;
            }
        }
    });
    
    // Специальная обработка для разных типов заданий
    if (templateId === 'task-template-quiz' && data.content && data.content.options) {
        const quizOptions = clone.querySelector('#quiz-options');
        if (quizOptions) {
            const multipleChoice = data.content.multiple_choice || false;
            quizOptions.innerHTML = '';
            
            data.content.options.forEach((option, index) => {
                const label = document.createElement('label');
                label.className = 'flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors mb-2';
                label.innerHTML = `
                    <input type="${multipleChoice ? 'checkbox' : 'radio'}" 
                           name="quiz-option" 
                           value="${option}" 
                           class="mr-3">
                    <span class="text-gray-700">${option}</span>
                `;
                quizOptions.appendChild(label);
            });
        }
    }
    
    if (templateId === 'task-template-puzzle' && data.content && data.content.pieces) {
        const puzzleGrid = clone.querySelector('#puzzle-grid');
        if (puzzleGrid) {
            puzzleGrid.innerHTML = '';
            
            data.content.pieces.forEach((piece, index) => {
                const pieceDiv = document.createElement('div');
                pieceDiv.className = 'puzzle-piece bg-white border border-gray-300 rounded-lg p-3 text-center cursor-move';
                pieceDiv.setAttribute('data-piece', index);
                pieceDiv.textContent = piece;
                puzzleGrid.appendChild(pieceDiv);
            });
        }
    }
    
    if (templateId === 'task-template-image' && data.content && data.content.required_elements) {
        const requiredElements = clone.querySelector('.required-elements');
        const requiredElementsList = clone.querySelector('.required-elements-list');
        
        if (requiredElements && requiredElementsList && data.content.required_elements.length > 0) {
            requiredElements.style.display = 'block';
            data.content.required_elements.forEach(element => {
                const li = document.createElement('li');
                li.textContent = element;
                requiredElementsList.appendChild(li);
            });
        }
    }
    
    if (templateId === 'task-template-code' && data.content && data.content.expected_output) {
        const expectedOutput = clone.querySelector('.expected-output');
        if (expectedOutput) {
            expectedOutput.style.display = 'block';
        }
    }
    
    if (templateId === 'task-template-cipher' && data.content && data.content.cipher_type) {
        const cipherTypeLabel = clone.querySelector('.cipher-type-label');
        if (cipherTypeLabel) {
            const cipherTypes = {
                'caesar': 'Шифр Цезаря',
                'atbash': 'Атбаш',
                'vigenere': 'Шифр Виженера',
                'morse': 'Азбука Морзе',
                'substitution': 'Простая замена'
            };
            cipherTypeLabel.textContent = cipherTypes[data.content.cipher_type] || data.content.cipher_type;
        }
        
        if (data.content.hint) {
            const hintContainer = clone.querySelector('.hint-container');
            if (hintContainer) {
                hintContainer.style.display = 'block';
            }
        }
    }
    
    if (templateId === 'task-template-location' && data.content) {
        if (data.content.coordinates) {
            const coordinatesContainer = clone.querySelector('.coordinates-container');
            if (coordinatesContainer) {
                coordinatesContainer.style.display = 'block';
            }
        }
        
        if (data.content.qr_code) {
            const qrContainer = clone.querySelector('.qr-container');
            if (qrContainer) {
                qrContainer.style.display = 'block';
            }
        }
    }
    
    // Показываем секцию с подсказками если они есть
    if (data.hints_available && data.hints_available > 0) {
        const hintsSection = clone.querySelector('.hints-section');
        if (hintsSection) {
            hintsSection.style.display = 'block';
        }
    }
    
    return clone;
}

// Получить значение по пути в объекте
function getValueByPath(obj, path) {
    return path.split('.').reduce((current, key) => {
        return current ? current[key] : undefined;
    }, obj);
}

// Функция для отображения задания
function displayTask(taskData) {
    console.log('Отображаем задание:', taskData);
    
    if (!taskData || !taskData.task) {
        displayTaskError('Данные задания не получены');
        return;
    }
    
    currentTask = taskData.task;
    currentTaskId = taskData.task.id;
    
    // Определяем шаблон в зависимости от типа задания
    let templateId;
    switch(currentTask.type) {
        case 'text': templateId = 'task-template-text'; break;
        case 'image': templateId = 'task-template-image'; break;
        case 'quiz': templateId = 'task-template-quiz'; break;
        case 'code': templateId = 'task-template-code'; break;
        case 'cipher': templateId = 'task-template-cipher'; break;
        case 'puzzle': templateId = 'task-template-puzzle'; break;
        case 'location': templateId = 'task-template-location'; break;
        default: templateId = 'task-template-text';
    }
    
    // Рендерим шаблон
    const content = renderTemplate(templateId, currentTask);
    const taskContent = document.getElementById('task-content');
    
    if (taskContent) {
        taskContent.innerHTML = '';
        taskContent.appendChild(content);
        
        // Показываем модальное окно
        const modal = document.getElementById('task-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
        
        // Инициализируем специфичные для типа функциональности
        initTaskSpecificFeatures();
    }
}

// Инициализация специфичных фич
function initTaskSpecificFeatures() {
    if (!currentTask) return;
    
    switch(currentTask.type) {
        case 'text':
            initTextTask();
            break;
        case 'image':
            initImageTask();
            break;
        case 'quiz':
            initQuizTask();
            break;
        case 'code':
            initCodeTask();
            break;
        case 'cipher':
            initCipherTask();
            break;
        case 'puzzle':
            initPuzzleTask();
            break;
        case 'location':
            initLocationTask();
            break;
    }
}

// Инициализация текстового задания
function initTextTask() {
    const textarea = document.getElementById('task-text-answer');
    const charCount = document.getElementById('text-char-count');
    
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
        charCount.textContent = textarea.value.length;
    }
}

// Инициализация задания с фото
function initImageTask() {
    // Инициализация уже выполнена в основном скрипте
}

// Инициализация викторины
function initQuizTask() {
    // Добавляем обработчики для вариантов ответа
    const options = document.querySelectorAll('#quiz-options label');
    options.forEach(option => {
        option.addEventListener('click', function(e) {
            if (e.target.type === 'radio' || e.target.type === 'checkbox') {
                return;
            }
            
            const input = this.querySelector('input');
            if (input.type === 'radio') {
                // Для радиокнопок снимаем выделение с других
                options.forEach(opt => {
                    opt.classList.remove('bg-blue-50', 'border-blue-300');
                });
                this.classList.add('bg-blue-50', 'border-blue-300');
                input.checked = true;
            } else {
                // Для чекбоксов переключаем класс
                this.classList.toggle('bg-blue-50');
                this.classList.toggle('border-blue-300');
                input.checked = !input.checked;
            }
        });
    });
}

// Инициализация задания с кодом
function initCodeTask() {
    const textarea = document.getElementById('code-answer');
    if (textarea) {
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = this.selectionStart;
                const end = this.selectionEnd;
                
                // Вставляем 4 пробела
                this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                
                // Перемещаем курсор
                this.selectionStart = this.selectionEnd = start + 4;
            }
        });
    }
}

// Инициализация шифра
function initCipherTask() {
    // Дополнительная функциональность для шифров
}

// Инициализация головоломки
function initPuzzleTask() {
    // Инициализируем перетаскивание для пазла
    const pieces = document.querySelectorAll('.puzzle-piece');
    let draggedPiece = null;
    
    pieces.forEach(piece => {
        piece.setAttribute('draggable', true);
        
        piece.addEventListener('dragstart', function(e) {
            draggedPiece = this;
            setTimeout(() => {
                this.classList.add('dragging');
            }, 0);
        });
        
        piece.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            draggedPiece = null;
        });
        
        piece.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        piece.addEventListener('drop', function(e) {
            e.preventDefault();
            if (draggedPiece && draggedPiece !== this) {
                // Меняем местами содержимое
                const temp = this.innerHTML;
                this.innerHTML = draggedPiece.innerHTML;
                draggedPiece.innerHTML = temp;
                
                // Меняем местами данные
                const tempData = this.getAttribute('data-piece');
                this.setAttribute('data-piece', draggedPiece.getAttribute('data-piece'));
                draggedPiece.setAttribute('data-piece', tempData);
            }
        });
    });
}

// Инициализация задания с локацией
function initLocationTask() {
    // Пытаемся получить текущую локацию сразу
    getCurrentLocation();
}

// Отображение ошибки
function displayTaskError(message = 'Не удалось загрузить задание') {
    const taskContent = document.getElementById('task-content');
    if (taskContent) {
        const errorTemplate = document.getElementById('task-template-error');
        const clone = errorTemplate.content.cloneNode(true);
        
        const errorMessage = clone.getElementById('error-message');
        if (errorMessage) {
            errorMessage.textContent = message;
        }
        
        taskContent.innerHTML = '';
        taskContent.appendChild(clone);
        
        const modal = document.getElementById('task-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
}

// Отображение результата
function displayTaskResult(result) {
    const taskContent = document.getElementById('task-content');
    if (taskContent) {
        const resultTemplate = document.getElementById('task-template-result');
        const clone = resultTemplate.content.cloneNode(true);
        
        const resultTitle = clone.getElementById('result-title');
        const resultPoints = clone.getElementById('result-points');
        const resultXp = clone.getElementById('result-xp');
        const resultMessage = clone.getElementById('result-message');
        
        if (resultTitle) {
            resultTitle.textContent = result.task?.title || 'Задание';
        }
        
        if (resultPoints) {
            resultPoints.textContent = `+${result.task?.points || 0} очков`;
        }
        
        if (resultXp) {
            resultXp.textContent = `+${result.task?.xp_earned || 0} XP`;
        }
        
        if (resultMessage) {
            resultMessage.textContent = result.message || 'Задание успешно выполнено!';
        }
        
        taskContent.innerHTML = '';
        taskContent.appendChild(clone);
        
        const modal = document.getElementById('task-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
}

// Повторная попытка загрузки задания
function retryLoadTask() {
    if (currentTaskId) {
        completeTask(currentTaskId);
    }
}

// Закрыть модальное окно и обновить страницу
function closeTaskModalAndRefresh() {
    closeTaskModal();
    setTimeout(() => {
        location.reload();
    }, 500);
}

// Получить текущую локацию для задания
let currentLocation = null;
let locationWatchId = null;

function getCurrentLocation() {
    if (!navigator.geolocation) {
        showNotification('Ошибка', 'Геолокация не поддерживается вашим браузером', 'error');
        return;
    }
    
    const locationInfo = document.getElementById('location-info');
    const submitBtn = document.getElementById('submit-location-btn');
    
    if (locationInfo) {
        locationInfo.innerHTML = '<div class="flex items-center mb-2"><i class="fas fa-sync-alt animate-spin mr-2"></i>Определяем местоположение...</div>';
    }
    
    if (submitBtn) {
        submitBtn.disabled = true;
    }
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            currentLocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                accuracy: position.coords.accuracy
            };
            
            if (locationInfo) {
                locationInfo.innerHTML = `
                    <div class="space-y-1">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Местоположение определено</span>
                        </div>
                        <div class="text-xs font-mono">
                            Широта: ${currentLocation.lat.toFixed(6)}
                        </div>
                        <div class="text-xs font-mono">
                            Долгота: ${currentLocation.lng.toFixed(6)}
                        </div>
                        <div class="text-xs text-gray-500">
                            Точность: ±${Math.round(currentLocation.accuracy)}м
                        </div>
                    </div>
                `;
            }
            
            if (submitBtn) {
                submitBtn.disabled = false;
            }
            
            // Начинаем слежение за локацией
            if (locationWatchId) {
                navigator.geolocation.clearWatch(locationWatchId);
            }
            
            locationWatchId = navigator.geolocation.watchPosition(
                function(pos) {
                    currentLocation = {
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                        accuracy: pos.coords.accuracy
                    };
                },
                function(error) {
                    console.warn('Ошибка геолокации:', error);
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
        },
        function(error) {
            console.error('Ошибка получения локации:', error);
            
            if (locationInfo) {
                locationInfo.innerHTML = `
                    <div class="text-red-600">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Не удалось определить местоположение
                    </div>
                `;
            }
            
            showNotification('Ошибка', 'Не удалось определить ваше местоположение', 'error');
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

// Показать локацию на карте
function showLocationOnMap() {
    if (currentTask && currentTask.content && currentTask.content.coordinates && navigationMap) {
        const lat = currentTask.content.coordinates.lat;
        const lng = currentTask.content.coordinates.lng;
        
        navigationMap.setView([lat, lng], 16);
        
        // Добавляем маркер целевой точки
        L.marker([lat, lng], {
            icon: L.divIcon({
                html: '<div style="width: 32px; height: 32px; background-color: red; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>',
                className: 'target-location-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            })
        }).addTo(navigationMap).bindPopup('Целевая точка задания');
        
        showNotification('Карта', 'Целевая точка показана на карте', 'info');
    }
}

// Отправить ответ для текстового задания
function submitTextTask() {
    const answer = document.getElementById('task-text-answer')?.value.trim();
    
    if (!answer) {
        showNotification('Внимание', 'Введите ответ', 'warning');
        return;
    }
    
    submitTaskCompletion({ answer: answer });
}

// Отправить фото
function submitPhotoTask() {
    const fileInput = document.getElementById('task-photo-input');
    const description = document.getElementById('photo-description')?.value || '';
    
    if (!fileInput.files || fileInput.files.length === 0) {
        showNotification('Внимание', 'Выберите фото', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('photo', fileInput.files[0]);
    formData.append('comment', description);
    formData.append('answer', 'photo_submission');
    
    showLoading('Загружаем фото...');
    
    apiFetch(`/api/tasks/${currentTaskId}/complete`, {
        method: 'POST',
        body: formData
    })
    .then(data => {
        if (data.success) {
            displayTaskResult(data.data);
        } else {
            showNotification('Ошибка', data.message || 'Ошибка загрузки', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка загрузки фото:', error);
        showNotification('Ошибка', 'Ошибка загрузки фото', 'error');
    })
    .finally(() => hideLoading());
}

// Отправить ответ на викторину
function submitQuizTask() {
    let answer;
    const multipleChoice = document.querySelector('input[name="quiz-option"]')?.type === 'checkbox';
    
    if (multipleChoice) {
        const selected = Array.from(document.querySelectorAll('input[name="quiz-option"]:checked'))
            .map(input => input.value);
        answer = selected.join(', ');
    } else {
        const selected = document.querySelector('input[name="quiz-option"]:checked');
        if (!selected) {
            showNotification('Внимание', 'Выберите вариант ответа', 'warning');
            return;
        }
        answer = selected.value;
    }
    
    submitTaskCompletion({ answer: answer });
}

// Отправить код
function submitCodeTask() {
    const code = document.getElementById('code-answer')?.value.trim();
    
    if (!code) {
        showNotification('Внимание', 'Введите код', 'warning');
        return;
    }
    
    submitTaskCompletion({ code: code });
}

// Отправить расшифрованный текст
function submitCipherTask() {
    const decodedText = document.getElementById('cipher-answer')?.value.trim();
    
    if (!decodedText) {
        showNotification('Внимание', 'Введите расшифрованный текст', 'warning');
        return;
    }
    
    submitTaskCompletion({ decoded_text: decodedText });
}

// Отправить решение головоломки
function submitPuzzleTask() {
    const pieces = Array.from(document.querySelectorAll('.puzzle-piece'))
        .map(piece => piece.getAttribute('data-piece'));
    
    submitTaskCompletion({ solution: pieces });
}

// Отправить локацию
function submitLocationTask() {
    if (!currentLocation) {
        showNotification('Внимание', 'Сначала определите ваше местоположение', 'warning');
        getCurrentLocation();
        return;
    }
    
    submitTaskCompletion({
        latitude: currentLocation.lat,
        longitude: currentLocation.lng
    });
    
    // Останавливаем слежение за локацией
    if (locationWatchId) {
        navigator.geolocation.clearWatch(locationWatchId);
        locationWatchId = null;
    }
}

// Запрос подсказки
function requestHint() {
    showLoading('Получаем подсказку...');
    
    apiFetch(`/api/tasks/${currentTaskId}/hint`)
    .then(data => {
        if (data.success) {
            showNotification('Подсказка', data.data.hint, 'info');
        } else {
            showNotification('Ошибка', data.message || 'Не удалось получить подсказку', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка получения подсказки:', error);
        showNotification('Ошибка', 'Ошибка получения подсказки', 'error');
    })
    .finally(() => hideLoading());
}

// Обновленная функция completeTask
async function completeTask(taskId) {
    console.log('Загружаем задание ID:', taskId);
    window.lastTaskId = taskId;
    currentTaskId = taskId;
    
    try {
        showLoading('Загружаем задание...');
        
        const response = await apiFetch(`/api/tasks/${taskId}`);
        
        if (response.success && response.data && response.data.task) {
            displayTask(response.data);
        } else {
            displayTaskError(response.message || 'Не удалось загрузить задание');
        }
    } catch (error) {
        console.error('Ошибка загрузки задания:', error);
        displayTaskError('Ошибка загрузки задания');
    } finally {
        hideLoading();
    }
}

// Обновленная функция submitTaskCompletion
async function submitTaskCompletion(data) {
    showLoading('Проверяем ответ...');
    
    try {
        const result = await apiFetch(`/api/tasks/${currentTaskId}/complete`, {
            method: 'POST',
            body: JSON.stringify(data)
        });
        
        if (result.success) {
            displayTaskResult(result.data);
        } else {
            showNotification('Ошибка', result.message || 'Ошибка при выполнении задания', 'error');
        }
    } catch (error) {
        console.error('Ошибка выполнения задания:', error);
        showNotification('Ошибка', 'Ошибка при выполнении задания', 'error');
    } finally {
        hideLoading();
    }
}

// Закрыть модальное окно задания
function closeTaskModal() {
    const modal = document.getElementById('task-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
    currentTask = null;
    currentTaskId = null;
}

// Добавить в DOMContentLoaded инициализацию
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация перетаскивания для пазлов
    initDragAndDrop();
    
    // Подключаем обработчики для новых типов заданий
    initTaskEventHandlers();
});

function initDragAndDrop() {
    // Код для перетаскивания пазлов уже в initPuzzleTask
}

function initTaskEventHandlers() {
    // Обработчики для разных типов заданий уже в соответствующих функциях
}
</script>