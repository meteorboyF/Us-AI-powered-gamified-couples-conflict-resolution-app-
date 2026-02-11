<div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-rose-50 py-12 px-4">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1
                        class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
                        Add Memory
                    </h1>
                    <p class="text-gray-600">Preserve a special moment ✨</p>
                </div>
                <a href="/vault" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    ← Back
                </a>
            </div>

            <!-- Upload Type Tabs -->
            <div class="flex gap-2 mb-8">
                <button wire:click="setUploadType('photo')"
                    class="flex-1 py-3 rounded-xl font-semibold transition-all {{ $uploadType === 'photo' ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    📸 Photo
                </button>
                <button wire:click="setUploadType('video')"
                    class="flex-1 py-3 rounded-xl font-semibold transition-all {{ $uploadType === 'video' ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    🎥 Video
                </button>
                <button wire:click="setUploadType('voice_note')"
                    class="flex-1 py-3 rounded-xl font-semibold transition-all {{ $uploadType === 'voice_note' ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    🎤 Voice
                </button>
                <button wire:click="setUploadType('text')"
                    class="flex-1 py-3 rounded-xl font-semibold transition-all {{ $uploadType === 'text' ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    📝 Text
                </button>
            </div>

            <form wire:submit.prevent="save">
                <!-- File Upload (for media types) -->
                @if($uploadType !== 'text')
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-3">
                            Upload File
                        </label>
                        <div
                            class="border-2 border-dashed border-purple-300 rounded-2xl p-8 text-center hover:border-purple-500 transition-colors">
                            <input type="file" wire:model="file"
                                accept="{{ $uploadType === 'photo' ? 'image/*' : ($uploadType === 'video' ? 'video/*' : 'audio/*') }}"
                                class="hidden" id="fileInput">
                            <label for="fileInput" class="cursor-pointer">
                                <div class="text-6xl mb-4">
                                    @if($uploadType === 'photo') 📸
                                    @elseif($uploadType === 'video') 🎥
                                    @else 🎤
                                    @endif
                                </div>
                                <p class="text-gray-700 font-semibold mb-2">Click to upload or drag and drop</p>
                                <p class="text-sm text-gray-500">
                                    @if($uploadType === 'photo') JPG, PNG, GIF, WEBP (max 5MB)
                                    @elseif($uploadType === 'video') MP4, MOV, AVI, WEBM (max 50MB)
                                    @else MP3, WAV, M4A, OGG (max 10MB)
                                    @endif
                                </p>
                            </label>
                            @if($file)
                                <div class="mt-4 p-3 bg-purple-50 rounded-xl">
                                    <p class="text-purple-700 font-semibold">{{ $file->getClientOriginalName() }}</p>
                                </div>
                            @endif
                        </div>
                        @error('file')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Title -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Title (optional)
                    </label>
                    <input type="text" wire:model="title" placeholder="Give this memory a title..."
                        class="w-full px-6 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-200 transition-all"
                        maxlength="100">
                    @error('title')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        {{ $uploadType === 'text' ? 'Your Memory' : 'Description (optional)' }}
                    </label>
                    <textarea wire:model="description" rows="{{ $uploadType === 'text' ? '8' : '4' }}"
                        placeholder="{{ $uploadType === 'text' ? 'Write about this special moment...' : 'Add a description...' }}"
                        class="w-full px-6 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-200 transition-all resize-none"
                        maxlength="{{ $uploadType === 'text' ? '1000' : '500' }}"></textarea>
                    <div class="flex justify-between items-center mt-2">
                        <span
                            class="text-sm text-gray-500">{{ strlen($description ?? '') }}/{{ $uploadType === 'text' ? '1000' : '500' }}
                            characters</span>
                        @error('description')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Visibility -->
                <div class="mb-8">
                    <label class="block text-gray-700 font-semibold mb-3">
                        Visibility
                    </label>
                    <div class="flex gap-4">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model="visibility" value="shared" class="hidden peer">
                            <div
                                class="p-4 border-2 border-gray-200 rounded-xl peer-checked:border-purple-500 peer-checked:bg-purple-50 transition-all text-center">
                                <div class="text-2xl mb-2">👥</div>
                                <div class="font-semibold text-gray-800">Shared</div>
                                <div class="text-sm text-gray-600">Both can see</div>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model="visibility" value="private" class="hidden peer">
                            <div
                                class="p-4 border-2 border-gray-200 rounded-xl peer-checked:border-purple-500 peer-checked:bg-purple-50 transition-all text-center">
                                <div class="text-2xl mb-2">🔐</div>
                                <div class="font-semibold text-gray-800">Private</div>
                                <div class="text-sm text-gray-600">Only you</div>
                            </div>
                        </label>
                    </div>
                </div>

                @if (session()->has('error'))
                    <div class="mb-6 p-4 bg-red-50 border-2 border-red-200 text-red-700 rounded-xl text-center">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    Save Memory
                </button>
            </form>
        </div>
    </div>
</div>