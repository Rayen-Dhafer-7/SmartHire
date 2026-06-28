<template>
  <div>
    <!-- Overlay Modal -->
    <teleport to="body">
      <div class="va-overlay" :class="{ show: isOpen }" @click.self="closeModal">
        <div class="va-modal" :class="{ show: isOpen }">

          <!-- Animated Orb -->
          <div class="orb-wrap">
            <div class="orb" :class="orbState">
              <div class="ring"></div>
              <div class="ring"></div>
              <div class="ring"></div>
              <div class="bars">
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
              </div>
            </div>
          </div>

          <p class="status-text">{{ statusText }}</p>
          <p class="timer">{{ timerDisplay }}</p>

          <audio ref="player" style="display:none; width:100%;"></audio>

          <div class="va-actions">
            <!-- Show Start Recording button when not recording and not finished -->
            <button
              v-if="!isRecording && !isFinished"
              class="btn-main btn-record"
              @click="startConversation"
            >
              Start Recording
            </button>
            
            <!-- Show Search Jobs button when finished -->
            <button
              v-if="isFinished"
              class="btn-main btn-search"
              @click="searchJobs"
            >
              Search Jobs
            </button>
            
            <!-- Show Play button if there's a recording -->
            <button v-if="hasRecording" class="btn-play" @click="playRecording">
              Play back recording
            </button>
            
            <!-- Close button always visible -->
            <button class="btn-close" @click="closeModal">Close</button>
          </div>
        </div>
      </div>
    </teleport>

    <!-- FAB Button -->
    <button class="va-fab" @click="openModal">
      <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24">
        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
        <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
        <line x1="12" y1="19" x2="12" y2="23"/>
        <line x1="8" y1="23" x2="16" y2="23"/>
      </svg>
    </button>
  </div>
</template>















<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  onSearchJobs: {
    type: Function,
    required: true
  }
});

/* ---------------- STATE ---------------- */
const isOpen = ref(false)
const isRecording = ref(false)
const hasRecording = ref(false)
const isFinished = ref(false)

const statusText = ref('Ready')
const orbState = ref('idle')
const seconds = ref(0)

const player = ref(null)
let workerName = localStorage.getItem("fullname")

let mediaRecorder = null
let chunks = []
let timerInterval = null
let typingInterval = null
let recordedAudioBlob = ref(null) // Make it a ref to track changes
let audioStream = null // Store the stream to stop it later

/* silence / speech detection */
let audioContext
let analyser
let silenceTimer
let listenAnimFrame
let userHasSpoken = false

// Dynamic detection
let noiseFloor = 0
let dynamicThreshold = 0

let currentStep = 0

/* ---------------- FEMALE VOICE ---------------- */
let femaleVoice = null

const loadFemaleVoice = () => {
  const voices = window.speechSynthesis.getVoices()
  if (voices.length === 0) return
  const preferred = ['Samantha', 'Karen', 'Moira', 'Tessa', 'Microsoft Zira', 'Microsoft Jenny']
  for (const name of preferred) {
    const match = voices.find(v => v.name.includes(name))
    if (match) { femaleVoice = match; return }
  }
  femaleVoice = voices.find(v => v.lang === 'en-US') || voices[0]
}

const playRecording = () => {
  if (player.value && player.value.src) {
    player.value.play()
  }
}

onMounted(() => {
  loadFemaleVoice()
  window.speechSynthesis.onvoiceschanged = loadFemaleVoice
})

const speak = (text, onEnd) => {
  const clean = text.replace(/[\u{1F000}-\u{1FFFF}]/gu, '').replace(/[\u2600-\u27FF]/g, '').trim()
  const utterance = new SpeechSynthesisUtterance(clean)
  utterance.rate = 1
  utterance.pitch = 1.1
  if (femaleVoice) utterance.voice = femaleVoice
  if (onEnd) utterance.onend = onEnd
  window.speechSynthesis.speak(utterance)
}

/* ---------------- QUESTIONS ---------------- */
const questions = [
  `Hi ${workerName || "there"}! I'm Sam, and I'll help you find your next great opportunity. Let's search for jobs together!`,
  `First, what skills should we look for? Tell me what you're good at or want to use.`,
  `Awesome! Now, how do you like to work — onsite, remote, or hybrid?`,
  `Got it. Any specific city or country you're targeting?`,
  `Perfect! And finally, what job titles are you looking for?`,
  `Thanks! I've got everything I need. Let me search for you.`
]

/* ---------------- TIMER ---------------- */
const timerDisplay = computed(() => {
  if(seconds.value === 0) return ""
  const m = Math.floor(seconds.value/60).toString().padStart(2,"0")
  const s = (seconds.value%60).toString().padStart(2,"0")
  return `${m}:${s}`
})

/* ---------------- TYPEWRITER ---------------- */
const typeText = (text) => {
  clearInterval(typingInterval)
  statusText.value = ""
  let i = 0
  typingInterval = setInterval(()=>{
    statusText.value += text[i]
    i++
    if(i >= text.length) clearInterval(typingInterval)
  }, 40)
}

/* ---------------- MODAL ---------------- */
const openModal = () => {
  isOpen.value = true
  isFinished.value = false
  currentStep = 0
  recordedAudioBlob.value = null // Reset blob
  chunks = []
  hasRecording.value = false
}

const closeModal = () => {
  stopEverything()
  window.speechSynthesis.cancel()
  clearInterval(typingInterval)
  isOpen.value = false
  resetState()
}

const resetState = () => {
  isRecording.value = false
  isFinished.value = false
  orbState.value = 'idle'
  statusText.value = 'Ready'
  seconds.value = 0
  chunks = []
  hasRecording.value = false
  userHasSpoken = false
  // Don't reset recordedAudioBlob here! It will be reset when opening modal
  if (player.value) player.value.src = ''
}

/* ---------------- CONVERSATION ---------------- */
const startConversation = () => {
  const greeting = questions[0]
  typeText(greeting)
  speak(greeting, () => {
    currentStep = 1
    askQuestion()
  })
}

/* ---------------- ASK QUESTION ---------------- */
const askQuestion = () => {
  const text = questions[currentStep]
  typeText(text)
  orbState.value = 'idle'

  speak(text, async () => {
    if (currentStep === questions.length - 1) {
      finishConversation()
      return
    }

    if (currentStep === 1 && !isRecording.value) {
      await startRecording()
    }

    startListeningForAnswer()
  })
}

/* ---------------- LISTEN FOR ANSWER ---------------- */
const SILENCE_DELAY = 2000

const startListeningForAnswer = async () => {
  stopListening()

  userHasSpoken = false
  statusText.value = "Listening... 🎤"
  orbState.value = 'recording'

  if (!audioContext || audioContext.state === 'closed') {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
    audioContext = new AudioContext()
    analyser = audioContext.createAnalyser()
    analyser.fftSize = 512
    const mic = audioContext.createMediaStreamSource(stream)
    mic.connect(analyser)
  }

  const data = new Uint8Array(analyser.fftSize)

  // Calibrate noise
  let samples = []
  for (let i = 0; i < 20; i++) {
    analyser.getByteTimeDomainData(data)

    let sum = 0
    for (let j = 0; j < data.length; j++) {
      let x = (data[j] - 128) / 128
      sum += x * x
    }
    let v = Math.sqrt(sum / data.length)
    samples.push(v)
  }

  noiseFloor = samples.reduce((a,b)=>a+b,0) / samples.length
  dynamicThreshold = noiseFloor + 0.02

  let speakingFrames = 0
  const MIN_SPEAK_FRAMES = 5

  const check = () => {
    analyser.getByteTimeDomainData(data)

    let sum = 0
    for (let i = 0; i < data.length; i++) {
      let x = (data[i] - 128) / 128
      sum += x * x
    }
    let volume = Math.sqrt(sum / data.length)

    if (volume > dynamicThreshold) {
      speakingFrames++

      if (speakingFrames > MIN_SPEAK_FRAMES) {
        if (!userHasSpoken) {
          userHasSpoken = true
          statusText.value = "Go ahead, I'm listening... 🎤"
        }

        clearTimeout(silenceTimer)
        silenceTimer = null
      }

    } else {
      speakingFrames = 0

      if (userHasSpoken && volume < noiseFloor + 0.01 && !silenceTimer) {
        silenceTimer = setTimeout(() => {
          stopListening()
          currentStep++
          if (currentStep < questions.length) {
            askQuestion()
          } else {
            finishConversation()
          }
        }, SILENCE_DELAY)
      }
    }

    listenAnimFrame = requestAnimationFrame(check)
  }

  listenAnimFrame = requestAnimationFrame(check)
}

/* ---------------- STOP LISTENING ---------------- */
const stopListening = () => {
  if (listenAnimFrame) {
    cancelAnimationFrame(listenAnimFrame)
    listenAnimFrame = null
  }
  clearTimeout(silenceTimer)
  silenceTimer = null
}

/* ---------------- RECORDING ---------------- */
const startRecording = async () => {
  try {
    // Stop any existing recording
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
      mediaRecorder.stop()
    }
    
    audioStream = await navigator.mediaDevices.getUserMedia({ audio: true })
    mediaRecorder = new MediaRecorder(audioStream)
    chunks = []

    mediaRecorder.ondataavailable = (e) => {
      if (e.data.size > 0) {
        chunks.push(e.data)
      }
    }
    
    mediaRecorder.onstop = () => {
      if (chunks.length > 0) {
        // Create blob from chunks
        recordedAudioBlob.value = new Blob(chunks, { type: "audio/webm" })
        const url = URL.createObjectURL(recordedAudioBlob.value)
        if (player.value) {
          player.value.src = url
          player.value.style.display = "block"
        }
        hasRecording.value = true
        orbState.value = "done"
        
        console.log("Recording saved:", {
          size: recordedAudioBlob.value.size,
          type: recordedAudioBlob.value.type
        })
      }
    }

    mediaRecorder.start()
    isRecording.value = true
    seconds.value = 0
    timerInterval = setInterval(() => seconds.value++, 1000)
    
  } catch (error) {
    console.error('Error starting recording:', error)
    statusText.value = 'Microphone access denied'
  }
}

/* ---------------- STOP RECORDING ---------------- */
const stopRecording = (discard = false) => {
  if (mediaRecorder && mediaRecorder.state !== "inactive") {
    mediaRecorder.stop()
  }
  
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }
  
  isRecording.value = false
  
  if (discard) {
    chunks = []
    recordedAudioBlob.value = null
    if (player.value) {
      player.value.src = ""
      player.value.style.display = "none"
    }
    hasRecording.value = false
  }
  
  // Stop the audio stream
  if (audioStream) {
    audioStream.getTracks().forEach(track => track.stop())
    audioStream = null
  }
}

/* ---------------- STOP EVERYTHING ---------------- */
const stopEverything = () => {
  stopListening()
  // Don't discard recording when stopping everything
  if (mediaRecorder && mediaRecorder.state !== "inactive") {
    mediaRecorder.stop()
  }
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }
  if (audioContext && audioContext.state !== 'closed') {
    audioContext.close()
  }
  if (audioStream) {
    audioStream.getTracks().forEach(track => track.stop())
    audioStream = null
  }
}

/* ---------------- FINISH ---------------- */
const finishConversation = () => {
  // Stop listening but keep recording
  stopListening()
  // Stop recording to finalize the blob
  if (mediaRecorder && mediaRecorder.state !== "inactive") {
    mediaRecorder.stop()
  }
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }
  isRecording.value = false
  
  orbState.value = "done"
  isFinished.value = true
  statusText.value = "Ready to search!"
  
  console.log("Recording ready:", {
    exists: !!recordedAudioBlob.value,
    size: recordedAudioBlob.value?.size,
    chunks: chunks.length
  })
}

/* ---------------- SEARCH JOBS ---------------- */
const searchJobs = () => {
  console.log("Search Jobs clicked, recording blob:", recordedAudioBlob.value)
  
  // Store blob before closing
  const blobToSend = recordedAudioBlob.value
  
  if (!blobToSend && chunks.length > 0) {
    // Try to create blob from chunks if exists
    const tempBlob = new Blob(chunks, { type: "audio/webm" })
    console.log("Created blob from existing chunks:", tempBlob.size)
    
    // Close modal
    closeModal();
    
    // Send the audio blob to parent
    if (props.onSearchJobs) {
      props.onSearchJobs(tempBlob);
    }
  } else if (blobToSend) {
    // Close modal
    closeModal();
    
    // Send the audio blob to parent
    if (props.onSearchJobs) {
      props.onSearchJobs(blobToSend);
    }
  } else {
    console.error("No recording found")
    closeModal();
    if (props.onSearchJobs) {
      props.onSearchJobs(null);
    }
  }
}

/* ---------------- CLEANUP ---------------- */
onUnmounted(() => {
  stopEverything()
  window.speechSynthesis.cancel()
  clearInterval(typingInterval)
})
</script>



















<style scoped>
.va-fab {
  position: fixed;
  bottom: 32px;
  right: 32px;
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: #4f46e5;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  transition: transform 0.2s, background 0.2s;
  box-shadow: 0 4px 16px rgba(79,70,229,0.4);
}
.va-fab:hover { transform: scale(1.08); background: #4338ca; }

.va-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s;
}
.va-overlay.show { opacity: 1; pointer-events: all; }

.va-modal {
  background: white;
  border-radius: 24px;
  padding: 40px 32px;
  width: 340px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  transform: scale(0.85);
  transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.va-modal.show { transform: scale(1); }

.orb-wrap {
  position: relative;
  width: 120px;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.orb {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  transition: background 0.6s, transform 0.3s;
}

.orb.idle { background: #4f46e5; }
.orb.recording { background: #4338ca; animation: pulse-orb 0.6s infinite ease-in-out; }
.orb.done { background: #4338ca; }

@keyframes pulse-orb {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.08); }
}

.ring {
  position: absolute;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 2px solid #ef4444;
  opacity: 0;
}
.orb.recording .ring { animation: ripple-out 1.4s infinite; }
.orb.recording .ring:nth-child(2) { animation-delay: 0.5s; }
.orb.recording .ring:nth-child(3) { animation-delay: 1s; }

@keyframes ripple-out {
  0% { transform: scale(1); opacity: 0.5; }
  100% { transform: scale(2.4); opacity: 0; }
}

.bars {
  display: flex;
  align-items: center;
  gap: 4px;
  height: 24px;
  z-index: 1;
}
.bar {
  width: 3px;
  border-radius: 3px;
  background: white;
  height: 6px;
}
.orb.recording .bar:nth-child(1) { animation: wave 0.7s infinite 0s; }
.orb.recording .bar:nth-child(2) { animation: wave 0.7s infinite 0.12s; }
.orb.recording .bar:nth-child(3) { animation: wave 0.7s infinite 0.24s; }
.orb.recording .bar:nth-child(4) { animation: wave 0.7s infinite 0.12s; }
.orb.recording .bar:nth-child(5) { animation: wave 0.7s infinite 0s; }

@keyframes wave {
  0%, 100% { height: 5px; }
  50% { height: 20px; }
}

.status-text {
  font-size: 15px;
  color: #374151;
  font-weight: 500;
  text-align: center;
  transition: all 0.3s;
  min-height: 60px;
}

.timer {
  font-size: 13px;
  color: #9ca3af;
  letter-spacing: 0.05em;
  min-height: 18px;
}

.va-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  width: 100%;
}

.btn-main {
  padding: 12px;
  border-radius: 14px;
  border: none;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  transition: transform 0.15s, background 0.2s;
}
.btn-main:active { transform: scale(0.97); }
.btn-record { background: #4f46e5; color: white; }
.btn-record:hover { background: #4338ca; }
.btn-search { background: #4f46e5; color: white; } /* Green color for search */
.btn-search:hover { background: #4338ca; }

.btn-play {
  padding: 10px;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  color: #1f2937;
  font-size: 14px;
  cursor: pointer;
  width: 100%;
}
.btn-play:hover { background: #f3f4f6; }

.btn-close {
  background: none;
  border: none;
  color: #9ca3af;
  font-size: 13px;
  cursor: pointer;
  padding: 6px;
  border-radius: 8px;
  width: 100%;
}
.btn-close:hover { background: #f3f4f6; color: #374151; }
</style>