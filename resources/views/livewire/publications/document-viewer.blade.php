<div>
    <div class="w-full h-full">
        @if(! $isAuthorized)
            <!-- Login CTA for non-authenticated users -->
            <div class="flex items-center justify-center h-full bg-gray-100 dark:bg-gray-950">
                <div class="text-center p-8">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-200 dark:bg-white/5 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">{{ __('Sign in to view documents online') }}</p>
                </div>
            </div>
        @elseif(! $fileName)
            <!-- No file selected -->
            <div class="flex items-center justify-center h-full bg-gray-100 dark:bg-gray-950">
                <div class="text-center p-8">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-200 dark:bg-white/5 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
<<<<<<< HEAD
                    <p class="text-gray-600 dark:text-gray-400">{{ __('Select a file to view') }}</p>
                </div>
            </div>
        @else
            <!-- Document Viewer Container - Full Height, No Header -->
            <div class="h-full w-full overflow-hidden bg-gray-100 dark:bg-gray-950">
                @switch($viewerType)
                    @case('pdf')
                        <!-- PDF Viewer - Direct embed -->
                        <iframe
                            src="{{ $fileUrl }}#toolbar=0&navpanes=0&scrollbar=1&view=FitH"
                            class="w-full h-full border-0"
                            type="application/pdf"
                        ></iframe>
                    @break

                    @case('epub')
                        <!-- EPUB Viewer using Epub.js -->
                        <div class="h-full flex flex-col">
                            <div id="epub-viewer-{{ $publicationId }}" class="flex-1 bg-white dark:bg-gray-900"></div>
                            <div id="epub-controls-{{ $publicationId }}" class="flex-shrink-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-white/5 px-4 py-3 flex justify-between items-center">
                                <button type="button" onclick="window.epubPrev{{ $publicationId }}()" class="px-4 py-2 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 text-gray-700 dark:text-white rounded-lg transition-colors text-sm font-medium">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                        {{ __('Previous') }}
                                    </span>
                                </button>
                                <span id="epub-location-{{ $publicationId }}" class="text-gray-500 dark:text-gray-400 text-sm"></span>
                                <button type="button" onclick="window.epubNext{{ $publicationId }}()" class="px-4 py-2 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 text-gray-700 dark:text-white rounded-lg transition-colors text-sm font-medium">
                                    <span class="flex items-center gap-2">
                                        {{ __('Next') }}
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </span>
                                </button>
=======
                    <script>
                        (function() {
                            const contentEl = document.getElementById('text-content-{{ $publicationId }}');
                            fetch('{{ $fileUrl }}')
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                                    }
                                    return response.text();
                                })
                                .then(text => {
                                    contentEl.textContent = text;
                                })
                                .catch(error => {
                                    console.error('Error loading file:', error);
                                    contentEl.innerHTML = `<div class="text-center"><p class="font-semibold text-red-500">{{ __("Error loading file") }}</p><p class="text-sm mt-2 text-gray-500">' + error.message + '</p></div>`;
                                });
                        })();
                    </script>
                @break

                @case('fb2')
                    <!-- FB2 Viewer using server-side XML-to-HTML conversion -->
                    <iframe
                        id="fb2-viewer-{{ $publicationId }}"
                        src="{{ route('files.convert-fb2', ['publication' => $publicationId, 'filename' => $this->encodeFileName($fileName)]) }}"
                        class="w-full h-full border-0"
                        sandbox="allow-same-origin"
                    ></iframe>
                    <script>
                        (function() {
                            const iframe = document.getElementById('fb2-viewer-{{ $publicationId }}');
                            iframe.onerror = function() {
                                iframe.style.display = 'none';
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'flex items-center justify-center h-full bg-gray-100 dark:bg-gray-950';
                                errorDiv.innerHTML = `<div class="text-center"><p class="font-semibold text-red-500">{{ __("Error loading FB2 file") }}</p><p class="text-sm mt-4"><a href="{{ route('files.download', ['publication' => $publicationId, 'filename' => $this->encodeFileName($fileName)]) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">{{ __("Download file instead") }}</a></p></div>`;
                                iframe.parentNode.insertBefore(errorDiv, iframe);
                            };
                        })();
                    </script>
                @break

                @case('document')
                    <!-- DOCX/DOC Viewer -->
                    @php
                        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $isDocx = $extension === 'docx';
                    @endphp

                    @if($isDocx)
                        <!-- DOCX Viewer using Mammoth.js -->
                        <div id="docx-viewer-{{ $publicationId }}" class="w-full h-full p-8 md:p-12 bg-white dark:bg-gray-950 overflow-auto">
                            <div class="max-w-3xl mx-auto text-gray-800 dark:text-gray-300">
                                <p class="text-gray-500">{{ __('Loading document...') }}</p>
>>>>>>> ed932cd8cb0e8344bfc916a8466b7f4af9640275
                            </div>
                        </div>
                        <script src="https://cdn.jsdelivr.net/npm/jszip@3/dist/jszip.min.js"></script>
                        <script src="https://cdn.jsdelivr.net/npm/epubjs@0.3/dist/epub.min.js"></script>
                        <script>
                            (function() {
                                const viewerId = 'epub-viewer-{{ $publicationId }}';
                                const book = ePub('{{ $fileUrl }}');
                                const rendition = book.renderTo(viewerId, {
                                    width: '100%',
                                    height: '100%',
                                    spread: 'always'
                                });

                                rendition.display().catch(err => {
                                    document.getElementById(viewerId).innerHTML =
                                        '<div class="flex items-center justify-center h-full"><div class="text-center text-red-500"><p class="font-semibold">Error loading EPUB</p><p class="text-sm mt-2 text-gray-500">' + err.message + '</p></div></div>';
                                });

                                window['epubPrev{{ $publicationId }}'] = () => rendition.prev();
                                window['epubNext{{ $publicationId }}'] = () => rendition.next();

                                rendition.on('relocated', (location) => {
                                    document.getElementById('epub-location-{{ $publicationId }}').textContent =
                                        'Page ' + (location.start.displayed.page || 1);
                                });
                            })();
                        </script>
                    @break

                    @case('text')
                        <!-- Text Viewer -->
                        <div class="w-full h-full p-8 md:p-12 bg-white dark:bg-gray-950 overflow-auto">
                            <div id="text-content-{{ $publicationId }}" class="max-w-3xl mx-auto text-gray-800 dark:text-gray-300 whitespace-pre-wrap font-serif text-base leading-relaxed">
                                <p class="text-gray-500">{{ __('Loading...') }}</p>
                            </div>
                        </div>
                        <script>
                            (function() {
                                const contentEl = document.getElementById('text-content-{{ $publicationId }}');
                                fetch('{{ $fileUrl }}')
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                                        }
                                        return response.text();
                                    })
                                    .then(text => {
                                        contentEl.textContent = text;
                                    })
                                    .catch(error => {
<<<<<<< HEAD
                                        console.error('Error loading file:', error);
                                        contentEl.innerHTML = '<div class="text-center"><p class="font-semibold text-red-500">{{ __("Error loading file") }}</p><p class="text-sm mt-2 text-gray-500">' + error.message + '</p></div>';
=======
                                        console.error('Error loading document:', error);
                                        let msg = error.name === 'AbortError' ? '{{ __("Loading timed out") }}' : error.message;
                                        viewerEl.innerHTML = `<div class="flex items-center justify-center h-full"><div class="text-center"><p class="font-semibold text-red-500">{{ __("Error loading document") }}</p><p class="text-sm mt-2 text-gray-500">' + msg + '</p><p class="text-sm mt-4 text-gray-400">{{ __("Try downloading the file instead") }}</p></div></div>`;
>>>>>>> ed932cd8cb0e8344bfc916a8466b7f4af9640275
                                    });
                            })();
                        </script>
                    @break

                    @case('fb2')
                        <!-- FB2 Viewer using server-side XML-to-HTML conversion -->
                        <iframe
                            id="fb2-viewer-{{ $publicationId }}"
                            src="{{ route('files.convert-fb2', ['publication' => $publicationId, 'filename' => $this->encodeFileName($fileName)]) }}"
                            class="w-full h-full border-0"
                            sandbox="allow-same-origin"
                        ></iframe>
                        <script>
                            (function() {
                                const iframe = document.getElementById('fb2-viewer-{{ $publicationId }}');
                                iframe.onerror = function() {
                                    iframe.style.display = 'none';
                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'flex items-center justify-center h-full bg-gray-100 dark:bg-gray-950';
<<<<<<< HEAD
                                    errorDiv.innerHTML = '<div class="text-center"><p class="font-semibold text-red-500">{{ __("Error loading FB2 file") }}</p><p class="text-sm mt-4"><a href="{{ route('files.download', ['publication' => $publicationId, 'filename' => $this->encodeFileName($fileName)]) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">{{ __("Download file instead") }}</a></p></div>';
=======
                                    errorDiv.innerHTML = `<div class="text-center"><p class="font-semibold text-red-500">{{ __("Error converting DOC file") }}</p><p class="text-sm mt-4"><a href="{{ route('files.download', ['publication' => $publicationId, 'filename' => $this->encodeFileName($fileName)]) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">{{ __("Download file instead") }}</a></p></div>`;
>>>>>>> ed932cd8cb0e8344bfc916a8466b7f4af9640275
                                    iframe.parentNode.insertBefore(errorDiv, iframe);
                                };
                            })();
                        </script>
                    @break

                    @case('document')
                        <!-- DOCX/DOC Viewer -->
                        @php
                            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            $isDocx = $extension === 'docx';
                        @endphp

                        @if($isDocx)
                            <!-- DOCX Viewer using Mammoth.js -->
                            <div id="docx-viewer-{{ $publicationId }}" class="w-full h-full p-8 md:p-12 bg-white dark:bg-gray-950 overflow-auto">
                                <div class="max-w-3xl mx-auto text-gray-800 dark:text-gray-300">
                                    <p class="text-gray-500">{{ __('Loading document...') }}</p>
                                </div>
                            </div>
                            <script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js"></script>
                            <script>
                                (function() {
                                    const viewerId = 'docx-viewer-{{ $publicationId }}';
                                    const viewerEl = document.getElementById(viewerId);
                                    const fileUrl = '{{ $fileUrl }}';

                                    if (!fileUrl) {
                                         viewerEl.innerHTML = '<div class="flex items-center justify-center h-full"><div class="text-center text-gray-500">{{ __("No file URL available") }}</div></div>';
                                         return;
                                    }

                                    // Timeout wrapper for fetch
                                    const fetchWithTimeout = (url, ms = 15000) => {
                                        const controller = new AbortController();
                                        const promise = fetch(url, { signal: controller.signal });
                                        const timeout = setTimeout(() => controller.abort(), ms);
                                        return promise.finally(() => clearTimeout(timeout));
                                    };

                                    fetchWithTimeout(fileUrl)
                                        .then(response => {
                                            if (!response.ok) {
                                                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                                            }
                                            return response.arrayBuffer();
                                        })
                                        .then(arrayBuffer => {
                                            return mammoth.convertToHtml({arrayBuffer: arrayBuffer});
                                        })
                                        .then(result => {
                                            viewerEl.innerHTML = '<div class="prose dark:prose-invert prose-gray max-w-3xl mx-auto">' + result.value + '</div>';
                                            if (result.messages.length > 0) {
                                                console.warn('Mammoth warnings:', result.messages);
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error loading document:', error);
                                            let msg = error.name === 'AbortError' ? '{{ __("Loading timed out") }}' : error.message;
                                            viewerEl.innerHTML = '<div class="flex items-center justify-center h-full"><div class="text-center"><p class="font-semibold text-red-500">{{ __("Error loading document") }}</p><p class="text-sm mt-2 text-gray-500">' + msg + '</p><p class="text-sm mt-4 text-gray-400">{{ __("Try downloading the file instead") }}</p></div></div>';
                                        });
                                })();
                            </script>
                        @else
                            <!-- DOC files - HTML viewer using PHPWord conversion with fallback to antiword -->
                            <iframe
                                id="doc-iframe-{{ $publicationId }}"
                                src="{{ route('files.convert-doc-html', ['publication' => $publicationId, 'filename' => $this->encodeFileName($fileName)]) }}"
                                class="w-full h-full border-0"
                                sandbox="allow-same-origin"
                            ></iframe>
                            <script>
                                (function() {
                                    const iframe = document.getElementById('doc-iframe-{{ $publicationId }}');
                                    iframe.onerror = function() {
                                        iframe.style.display = 'none';
                                        const errorDiv = document.createElement('div');
                                        errorDiv.className = 'flex items-center justify-center h-full bg-gray-100 dark:bg-gray-950';
                                        errorDiv.innerHTML = '<div class="text-center"><p class="font-semibold text-red-500">{{ __("Error converting DOC file") }}</p><p class="text-sm mt-4"><a href="{{ route('files.download', ['publication' => $publicationId, 'filename' => $this->encodeFileName($fileName)]) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">{{ __("Download file instead") }}</a></p></div>';
                                        iframe.parentNode.insertBefore(errorDiv, iframe);
                                    };
                                })();
                            </script>
                        @endif
                    @break

                    @case('djvu')
                        <!-- DJVU Viewer using djvu.js -->
                        <div class="h-full flex flex-col">
                            <div id="djvu-viewer-{{ $publicationId }}" class="flex-1 bg-white dark:bg-gray-900"></div>
                            <div class="flex-shrink-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-white/5 px-4 py-3 flex justify-between items-center">
                                <button type="button" onclick="window.djvuPrev{{ $publicationId }}()" class="px-4 py-2 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 text-gray-700 dark:text-white rounded-lg transition-colors text-sm font-medium">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                        {{ __('Previous') }}
                                    </span>
                                </button>
                                <span id="djvu-page-{{ $publicationId }}" class="text-gray-500 dark:text-gray-400 text-sm">{{ __('Loading...') }}</span>
                                <button type="button" onclick="window.djvuNext{{ $publicationId }}()" class="px-4 py-2 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 text-gray-700 dark:text-white rounded-lg transition-colors text-sm font-medium">
                                    <span class="flex items-center gap-2">
                                        {{ __('Next') }}
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </span>
                                </button>
                            </div>
                        </div>
                        <script src="https://cdn.jsdelivr.net/npm/djvu@0.8.3/dist/djvu.js"></script>
                        <script>
                            (function() {
                                const viewerId = 'djvu-viewer-{{ $publicationId }}';
                                const pageInfoId = 'djvu-page-{{ $publicationId }}';
                                let viewer = null;

                                fetch('{{ $fileUrl }}')
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('HTTP ' + response.status);
                                        }
                                        return response.arrayBuffer();
                                    })
                                    .then(buffer => {
                                        const doc = new DjVu.Document(buffer);
                                        viewer = new DjVu.Viewer();
                                        viewer.render(document.getElementById(viewerId));
                                        viewer.loadDocument(doc);

                                        function updatePageInfo() {
                                            if (viewer) {
                                                const pageNum = viewer.getCurrentPageNumber();
                                                const totalPages = doc.getPagesQuantity();
                                                document.getElementById(pageInfoId).textContent =
                                                    '{{ __("Page") }} ' + pageNum + ' / ' + totalPages;
                                            }
                                        }

                                        window['djvuPrev{{ $publicationId }}'] = () => {
                                            viewer.prevPage();
                                            updatePageInfo();
                                        };

                                        window['djvuNext{{ $publicationId }}'] = () => {
                                            viewer.nextPage();
                                            updatePageInfo();
                                        };

                                        updatePageInfo();
                                    })
                                    .catch(error => {
                                        console.error('Error loading DJVU:', error);
                                        document.getElementById(viewerId).innerHTML =
                                            '<div class="flex items-center justify-center h-full"><div class="text-center"><p class="font-semibold text-red-500">{{ __("Error loading DJVU file") }}</p><p class="text-sm mt-2 text-gray-500">' + error.message + '</p></div></div>';
                                    });
                            })();
                        </script>
                    @break

                    @default
                        <!-- Unsupported Format -->
                        <div class="w-full h-full flex items-center justify-center">
                            <div class="text-center p-8">
                                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-200 dark:bg-white/5 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300 font-medium">
                                    {{ __('This file format is not supported for online viewing') }}
                                </p>
                                <p class="text-sm text-gray-500 mt-2">
                                    {{ __('Please download the file to view it') }}
                                </p>
                                <a
                                    href="{{ route('files.download', ['publication' => $publicationId, 'filename' => $this->encodeFileName($fileName)]) }}"
                                    class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors text-sm font-medium"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    {{ __('Download File') }}
                                </a>
                            </div>
                        </div>
                @endswitch
            </div>
        @endif
    </div>
</div>
