<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .translation-container {
            max-width: 1200px;
            margin: 100px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgb(128, 219, 239);
            padding: 20px;
        }
        .form-control {
            border-radius: 5px;
        }
        h2 {
            margin-top: 30px;
        }
        .result {
            background-color: #f1f1f1;
            padding: 15px;
            border-radius: 5px;
        }
        .voice-icon {
            cursor: pointer;
            font-size: 24px;
            color: #007bff;
            margin-left: 10px; /* Add margin to separate icon from input */
        }
        .voice-icon:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="translation-container mt-5">
        <h1 class="text-center">Translate Achraf</h1>
        <div class="row">
            <div class="col-6">
                <form id="translation-form" method="POST" onsubmit="return false;">
                    @csrf
                    <div class="mb-3">
                        <label for="source-language" class="form-label">Source Language:</label>
                        <select id="source-language" name="source-language" class="form-select" required>
                            <option value="en">English</option>
                            <option value="fr">French</option>
                            <option value="ar">Arabic</option>
                            <option value="es">Spanish</option>
                            <option value="de">German</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <input type="text" placeholder="Write..." id="phrase" class="form-control" required>
                        <!-- Voice Play Icon for Source Language -->
                        <i id="play-source-voice" class="fas fa-volume-up voice-icon" title="Play Voice"></i>
                        <!-- Microphone Icon -->
                        <i id="start-recording" class="fas fa-microphone voice-icon" title="Start Recording"></i>
                        <i id="stop-recording" class="fas fa-stop voice-icon" title="Stop Recording" style="display: none;"></i>
                    </div>
                </form>
            </div>
            <div class="col-6">
                <div id="translation-result">
                    <div class="mb-3">
                        <label for="language" class="form-label">Target Language:</label>
                        <select id="language" name="language" class="form-select" required>
                            <option value="en">English</option>
                            <option value="fr">French</option>
                            <option value="ar">Arabic</option>
                            <option value="es">Spanish</option>
                            <option value="de">German</option>
                        </select>
                    </div>

                    <!-- Card for Translation Result -->
                    <div id="result-text" class="card mt-3">
                        <div class="card-body">
                            <p class="card-text">No translation available yet.</p>
                        </div>
                    </div>

                    <!-- Voice Play Icon for Translation Result -->
                    <i id="play-voice" class="fas fa-volume-up voice-icon mt-3" title="Play Voice"></i>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer py-3">
        <div class="container text-center">
            <span class="text-muted">&copy; {{ date('Y') }} <a href="https://www.linkedin.com/in/achraf-el-hasnaoui-3364a91b6/">
                ACHRAF EL HASNAOUI  </a> . Tous droits réservés.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize SpeechRecognition
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        recognition.continuous = false; // Stop after one sentence
        recognition.interimResults = false; // Don't show interim results

        document.getElementById('phrase').addEventListener('input', function() {
            const phrase = this.value;
            const language = document.getElementById('language').value;
            const sourceLanguage = document.getElementById('source-language').value;

            if (phrase) {
                fetchTranslation(phrase, sourceLanguage, language);
            } else {
                displayTranslation('No translation available yet.');
            }
        });

        async function fetchTranslation(phrase, sourceLanguage, targetLanguage) {
            try {
                const response = await fetch('{{ route('translate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ phrase, 'source-language': sourceLanguage, language: targetLanguage })
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();
                console.log('Translation response:', data); // Log the full response
                displayTranslation(data.translatedPhrase);
            } catch (error) {
                console.error('Error fetching translation:', error);
                displayTranslation('Error: Unable to translate.');
            }
        }

        function displayTranslation(translatedPhrase) {
            const resultText = translatedPhrase || 'Error: Unable to translate.';
            document.getElementById('result-text').querySelector('.card-text').textContent = resultText;
        }

        // Helper function to get the correct voice based on the selected language
        function getVoice(language) {
            const voices = speechSynthesis.getVoices();
            return voices.find(voice => voice.lang === language) || voices[0]; // Fallback to the first available voice
        }

        // Voice-over functionality for Translation Result
        document.getElementById('play-voice').addEventListener('click', function() {
            const translatedText = document.getElementById('result-text').querySelector('.card-text').textContent;
            console.log('Translated text for speech synthesis:', translatedText); // Log translated text
            if (translatedText && translatedText !== 'No translation available yet.') {
                const utterance = new SpeechSynthesisUtterance(translatedText);
                utterance.voice = getVoice(document.getElementById('language').value); // Set the voice
                speechSynthesis.speak(utterance);
            } else {
                alert('Please translate text before playing voice.');
            }
        });

        // Voice-over functionality for Source Language
        document.getElementById('play-source-voice').addEventListener('click', function() {
            const sourceText = document.getElementById('phrase').value;
            console.log('Source text for speech synthesis:', sourceText); // Log source text
            if (sourceText) {
                const utterance = new SpeechSynthesisUtterance(sourceText);
                utterance.voice = getVoice(document.getElementById('source-language').value); // Set the voice
                speechSynthesis.speak(utterance);
            } else {
                alert('Please enter text before playing voice.');
            }
        });

        // Start recording from the microphone
        document.getElementById('start-recording').addEventListener('click', function() {
            recognition.start();
            document.getElementById('start-recording').style.display = 'none';
            document.getElementById('stop-recording').style.display = 'inline';
        });

        // Stop recording
        document.getElementById('stop-recording').addEventListener('click', function() {
            recognition.stop();
            document.getElementById('start-recording').style.display = 'inline';
            document.getElementById('stop-recording').style.display = 'none';
        });

        // Capture the result from the microphone
        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            document.getElementById('phrase').value = transcript; // Set the input value to the transcript
            recognition.stop(); // Stop recognition after capturing input
            document.getElementById('start-recording').style.display = 'inline';
            document.getElementById('stop-recording').style.display = 'none';
            console.log('Transcribed text:', transcript); // Log transcribed text
            fetchTranslation(transcript, document.getElementById('source-language').value, document.getElementById('language').value); // Fetch translation for transcribed text
        };

        recognition.onend = function() {
            document.getElementById('start-recording').style.display = 'inline';
            document.getElementById('stop-recording').style.display = 'none';
        };

        recognition.onerror = function(event) {
            console.error('Speech recognition error:', event.error);
            alert('Error occurred in recognition: ' + event.error);
            document.getElementById('start-recording').style.display = 'inline';
            document.getElementById('stop-recording').style.display = 'none';
        };
    </script>
</body>
</html>
