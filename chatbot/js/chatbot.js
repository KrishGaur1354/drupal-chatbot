(function ($, Drupal) {
  Drupal.behaviors.chatbot = {
    attach: function (context) {
      once('chatbot', 'body', context).forEach(function () {
        const chatbot = document.querySelector(".chatbot");
        const chatbotToggler = document.querySelector(".chatbot-toggler");
        const closeBtn = document.querySelector(".close-btn");
        const chatbox = document.querySelector(".chatbox");
        const chatInput = document.querySelector(".chat-input textarea");
        const sendChatBtn = document.querySelector("#send-btn");
        const micBtn = document.querySelector("#mic-btn");
        const resizeHandle = document.querySelector(".resize-handle");

        if (!chatbot || !chatbotToggler || !closeBtn || !chatbox || !chatInput || !sendChatBtn || !micBtn || !resizeHandle) {
          console.error("One or more chatbot elements not found");
          return;
        }

        // Apply customizations
        const settings = drupalSettings.chatbot;
        if (settings.color) {
          document.documentElement.style.setProperty('--primary-color', settings.color);
        }

        if (settings.logo_url) {
          const logoImg = chatbot.querySelector('.drupal-logo');
          if (logoImg) {
            logoImg.src = settings.logo_url;
          }
        }

        if (!settings.enable_speech_to_text) {
          micBtn.style.display = 'none';
        }

        let API_KEY = null;
        let API_URL = null;

        // Fetch API key and endpoint from the REST API
        fetch('/api/chatbot/settings')
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
          })
          .then(data => {
            API_KEY = data.api_key;
            API_URL = data.endpoint;
            console.log('Chatbot settings loaded successfully');
          })
          .catch(error => {
            console.error('Error fetching chatbot settings:', error);
          });

        let userMessage = null;
        const inputInitHeight = chatInput.scrollHeight;

        const createChatLi = (message, className) => {
          const chatLi = document.createElement("li");
          chatLi.classList.add("chat", `${className}`);
          let chatContent = className === "outgoing" ? `<p></p>` : `<span class="material-symbols-outlined">smart_toy</span><p></p>`;
          chatLi.innerHTML = chatContent;
          chatLi.querySelector("p").textContent = message;
          return chatLi;
        }

        const generateResponse = (chatElement) => {
          const messageElement = chatElement.querySelector("p");

          if (!API_KEY || !API_URL) {
            messageElement.textContent = "Chatbot is not configured properly. Please try again later.";
            messageElement.classList.add("error");
            return;
          }

          const requestOptions = {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "Authorization": `Bearer ${API_KEY}`
            },
            body: JSON.stringify({
              model: "gpt-4",
              messages: [{role: "user", content: userMessage}],
            })
          }

          fetch(API_URL, requestOptions)
            .then(res => res.json())
            .then(data => {
              messageElement.textContent = data.choices[0].message.content.trim();
            })
            .catch(() => {
              messageElement.classList.add("error");
              messageElement.textContent = "Oops! Something went wrong. Please try again.";
            })
            .finally(() => chatbox.scrollTo(0, chatbox.scrollHeight));
        }

        const handleChat = () => {
          userMessage = chatInput.value.trim();
          if (!userMessage) return;

          chatInput.value = "";
          chatInput.style.height = `${inputInitHeight}px`;

          chatbox.appendChild(createChatLi(userMessage, "outgoing"));
          chatbox.scrollTo(0, chatbox.scrollHeight);

          setTimeout(() => {
            const incomingChatLi = createChatLi("Thinking...", "incoming");
            chatbox.appendChild(incomingChatLi);
            chatbox.scrollTo(0, chatbox.scrollHeight);
            generateResponse(incomingChatLi);
          }, 600);
        }

        // Speech Recognition
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();

        recognition.onstart = () => {
          micBtn.classList.add('listening');
        };

        recognition.onresult = (event) => {
          const current = event.resultIndex;
          const transcript = event.results[current][0].transcript;
          chatInput.value = transcript;
          handleChat();
        };

        recognition.onend = () => {
          micBtn.classList.remove('listening');
        };

        micBtn.addEventListener('click', () => {
          recognition.start();
        });

        // Resizing functionality
        let isResizing = false;
        let lastDownX, lastDownY;

        resizeHandle.addEventListener('mousedown', (e) => {
          isResizing = true;
          lastDownX = e.clientX;
          lastDownY = e.clientY;
        });

        document.addEventListener('mousemove', (e) => {
          if (!isResizing) return;

          const newWidth = chatbot.offsetWidth + (e.clientX - lastDownX);
          const newHeight = chatbot.offsetHeight + (e.clientY - lastDownY);

          chatbot.style.width = `${newWidth}px`;
          chatbot.style.height = `${newHeight}px`;
          chatbox.style.height = `${newHeight - 120}px`; // Adjust for header and input area

          lastDownX = e.clientX;
          lastDownY = e.clientY;
        });

        document.addEventListener('mouseup', () => {
          isResizing = false;
        });

        chatInput.addEventListener("input", () => {
          chatInput.style.height = `${inputInitHeight}px`;
          chatInput.style.height = `${chatInput.scrollHeight}px`;
        });

        chatInput.addEventListener("keydown", (e) => {
          if (e.key === "Enter" && !e.shiftKey && window.innerWidth > 800) {
            e.preventDefault();
            handleChat();
          }
        });

        sendChatBtn.addEventListener("click", handleChat);
        closeBtn.addEventListener("click", () => document.body.classList.remove("show-chatbot"));
        chatbotToggler.addEventListener("click", () => document.body.classList.toggle("show-chatbot"));
      });

    }
  };
})(jQuery, Drupal);
