@php
    // URL-safe base64 encoding helper
    $urlSafeBase64 = function($str) {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    };
@endphp

<div class="w-full">
    @if(! $isAuthorized)
        <!-- Login CTA for non-authenticated users -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-600 p-6 rounded-lg">
            <p class="text-gray-700 dark:text-gray-300">
                {{ __('Sign in to view documents online') }}
            </p>
        </div>
    @elseif(! $fileName)
        <!-- No file selected -->
        <div class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 p-8 rounded-lg text-center">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-600 dark:text-gray-400">{{ __('Select a file to view') }}</p>
        </div>
    @else
        <!-- Document Viewer Container -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <!-- Viewer Header -->
            <div class="bg-gray-100 dark:bg-gray-700 px-6 py-4 border-b border-gray-300 dark:border-gray-600">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $fileName }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $mimeType ?? 'application/octet-stream' }} • {{ number_format(($fileSizeBytes ?? 0) / 1024 / 1024, 2) }} MB
                    </p>
                </div>
            </div>

            <!-- Viewer Content -->
            <div class="overflow-auto bg-gray-900">
                @switch($viewerType)
                    @case('pdf')
                        <!-- PDF Viewer - Direct embed -->
                        <iframe
                            src="{{ $fileUrl }}#toolbar=1&navpanes=1&scrollbar=1"
                            class="w-full border-0"
                            style="height: calc(100vh - 140px);"
                            type="application/pdf"
                        ></iframe>
                    @break

                    @case('epub')
                        <!-- EPUB Viewer using Epub.js -->
                        <div id="epub-viewer-{{ $publicationId }}" class="w-full bg-white dark:bg-gray-800" style="height: 80vh;"></div>
                        <div id="epub-controls-{{ $publicationId }}" class="bg-gray-100 dark:bg-gray-700 p-4 flex justify-between items-center">
                            <button type="button" onclick="window.epubPrev{{ $publicationId }}()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                ← {{ __('Previous') }}
                            </button>
                            <span id="epub-location-{{ $publicationId }}" class="text-gray-700 dark:text-gray-300"></span>
                            <button type="button" onclick="window.epubNext{{ $publicationId }}()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                {{ __('Next') }} →
                            </button>
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
                                        '<div class="flex items-center justify-center h-full"><div class="text-center text-red-600"><p class="font-bold">Error loading EPUB</p><p class="text-sm mt-2">' + err.message + '</p></div></div>';
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
                        <div class="w-full h-screen p-8 bg-white dark:bg-gray-900 overflow-auto">
                            <div id="text-content-{{ $publicationId }}" class="prose dark:prose-invert max-w-4xl mx-auto text-gray-800 dark:text-gray-200 whitespace-pre-wrap font-serif text-base leading-relaxed">
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
                                        console.error('Error loading file:', error);
                                        contentEl.innerHTML = '<div class="text-red-600"><p class="font-bold">{{ __("Error loading file") }}</p><p class="text-sm mt-2">' + error.message + '</p></div>';
                                    });
                            })();
                        </script>
                    @break

                    @case('fb2')
                        <!-- FB2 Viewer using server-side XML-to-HTML conversion -->
                        <div class="w-full h-screen overflow-auto">
                            <iframe
                                id="fb2-viewer-{{ $publicationId }}"
                                src="{{ route('files.convert-fb2', ['publication' => $publicationId, 'filename' => $urlSafeBase64($fileName)]) }}"
                                class="w-full border-0"
                                style="height: calc(100vh - 140px);"
                                sandbox="allow-same-origin"
                            ></iframe>
                        </div>
                        <script>
                            (function() {
                                const iframe = document.getElementById('fb2-viewer-{{ $publicationId }}');
                                iframe.onerror = function() {
                                    iframe.style.display = 'none';
                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'flex items-center justify-center h-full';
                                    errorDiv.innerHTML = '<div class="text-center text-red-600"><p class="font-bold">{{ __("Error loading FB2 file") }}</p><p class="text-sm mt-4"><a href="{{ route('files.download', ['publication' => $publicationId, 'filename' => $urlSafeBase64($fileName)]) }}" class="text-blue-600 hover:underline">{{ __("Download file instead") }}</a></p></div>';
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
                            <div id="docx-viewer-{{ $publicationId }}" class="w-full h-screen p-8 bg-white dark:bg-gray-900 overflow-auto">
                                <div class="prose dark:prose-invert max-w-4xl mx-auto">
                                    <p class="text-gray-500">{{ __('Loading document...') }}</p>
                                </div>
                            </div>
                            <script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js"></script>
                            <script>
                                (function() {
                                    const viewerId = 'docx-viewer-{{ $publicationId }}';
                                    const viewerEl = document.getElementById(viewerId);

                                    fetch('{{ $fileUrl }}')
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
                                            viewerEl.innerHTML = '<div class="prose dark:prose-invert max-w-4xl mx-auto">' + result.value + '</div>';
                                            if (result.messages.length > 0) {
                                                console.warn('Mammoth warnings:', result.messages);
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error loading document:', error);
                                            viewerEl.innerHTML = '<div class="flex items-center justify-center h-full"><div class="text-center text-red-600"><p class="font-bold">{{ __("Error loading document") }}</p><p class="text-sm mt-2">' + error.message + '</p><p class="text-sm mt-4 text-gray-600">{{ __("Try downloading the file instead") }}</p></div></div>';
                                        });
                                })();
                            </script>
                        @else
                            <!-- DOC files - HTML viewer using PHPWord conversion with fallback to antiword -->
                            <div id="doc-viewer-{{ $publicationId }}" class="w-full h-screen overflow-auto">
                                <iframe
                                    id="doc-iframe-{{ $publicationId }}"
                                    src="{{ route('files.convert-doc-html', ['publication' => $publicationId, 'filename' => $urlSafeBase64($fileName)]) }}"
                                    class="w-full border-0"
                                    style="height: calc(100vh - 140px);"
                                    sandbox="allow-same-origin"
                                ></iframe>
                            </div>
                            <script>
                                (function() {
                                    const iframe = document.getElementById('doc-iframe-{{ $publicationId }}');
                                    iframe.onerror = function() {
                                        iframe.style.display = 'none';
                                        const errorDiv = document.createElement('div');
                                        errorDiv.className = 'flex items-center justify-center h-full';
                                        errorDiv.innerHTML = '<div class="text-center text-red-600"><p class="font-bold">{{ __("Error converting DOC file") }}</p><p class="text-sm mt-4"><a href="{{ route('files.download', ['publication' => $publicationId, 'filename' => $urlSafeBase64($fileName)]) }}" class="text-blue-600 hover:underline">{{ __("Download file instead") }}</a></p></div>';
                                        iframe.parentNode.insertBefore(errorDiv, iframe);
                                    };
                                })();
                            </script>
                        @endif
                    @break

                    @case('djvu')
                        <!-- DJVU Viewer using djvu.js -->
                        <div id="djvu-viewer-{{ $publicationId }}" class="w-full bg-gray-800" style="height: 80vh; position: relative;"></div>
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 flex justify-between items-center">
                            <button type="button" onclick="window.djvuPrev{{ $publicationId }}()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                ← {{ __('Previous') }}
                            </button>
                            <span id="djvu-page-{{ $publicationId }}" class="text-gray-700 dark:text-gray-300">{{ __('Loading...') }}</span>
                            <button type="button" onclick="window.djvuNext{{ $publicationId }}()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                {{ __('Next') }} →
                            </button>
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
                                            '<div class="flex items-center justify-center h-full"><div class="text-center text-red-600"><p class="font-bold">{{ __("Error loading DJVU file") }}</p><p class="text-sm mt-2">' + error.message + '</p></div></div>';
                                    });
                            })();
                        </script>
                    @break

                    @default
                        <!-- Unsupported Format -->
                        <div class="w-full h-96 flex items-center justify-center bg-gray-100 dark:bg-gray-800">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-gray-600 dark:text-gray-400">
                                    {{ __('This file format is not supported for online viewing') }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                                    {{ __('Please download the file to view it') }}
                                </p>
                                <a
                                    href="{{ route('files.download', ['publication' => $publicationId, 'filename' => $urlSafeBase64($fileName)]) }}"
                                    class="inline-block mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                >
                                    {{ __('Download File') }}
                                </a>
                            </div>
                        </div>
                @endswitch
            </div>
        </div>
    @endif
</div>
