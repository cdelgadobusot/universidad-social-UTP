/* --------------------------------------------------
   script.js – Funciones generales para “Universidad Social”
---------------------------------------------------*/

// Utilidades ---------------------------------------------------------------
function qs(sel, scope = document) {
  return scope.querySelector(sel);
}
function qsa(sel, scope = document) {
  return [...scope.querySelectorAll(sel)];
}

// Simulador de envío de formularios ---------------------------------------
function handleFormSubmission(form) {
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const successMessage = form.dataset.success || 'Operación realizada correctamente.';

    // Validación mínima: chequear campos required
    const invalidFields = qsa('[required]', form).filter((el) => !el.value.trim());
    if (invalidFields.length) {
      showAlert('Por favor, completa todos los campos obligatorios.', 'error', form);
      invalidFields[0].focus();
      return;
    }

    // Simula latencia / conexión (1s)
    setTimeout(() => {
      showAlert(successMessage, 'success', form);
      form.reset();
    }, 1000);
  });
}

// Alerta dinámica ----------------------------------------------------------
function showAlert(msg, type = 'success', context = document.body) {
  const alert = document.createElement('div');
  alert.className = `alert alert-${type} animate-fade-in`;
  alert.textContent = msg;
  context.prepend(alert);
  // Remover después de 4s
  setTimeout(() => alert.remove(), 4000);
}

// ChatBOT placeholder ------------------------------------------------------
function initChatBot() {
  const botBtn = document.createElement('button');
  botBtn.className = 'btn btn-primary';
  botBtn.id = 'chatbot-toggle';
  botBtn.style.position = 'fixed';
  botBtn.style.bottom = '1.5rem';
  botBtn.style.right = '1.5rem';
  botBtn.textContent = 'bot ☎';
  document.body.appendChild(botBtn);

  botBtn.addEventListener('click', () => {
    alert('🤖 Hola, soy tu asistente virtual. ¿En qué puedo ayudarte?');
  });
}

document.addEventListener('DOMContentLoaded', () => {
  // Inicializa formularios
  qsa('form[data-success]').forEach(handleFormSubmission);
  // Chatbot
  initChatBot();
});
