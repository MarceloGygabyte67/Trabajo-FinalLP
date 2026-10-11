document.addEventListener('DOMContentLoaded', () => {
  const card = document.getElementById('card3D');
  const glare = document.getElementById('cardGlare');
  const form = document.getElementById('formularioSolicitud');
  const btnEnviar = document.getElementById('btnEnviar');

  // Elementos del Modal de Evaluación
  const popup = document.getElementById('statusPopup');
  const popupIcon = document.getElementById('popupIcon');
  const popupVeredicto = document.getElementById('popupVeredicto');
  const popupTitle = document.getElementById('popupTitle');
  const popupMessage = document.getElementById('popupMessage');
  const popupCloseBtn = document.getElementById('popupCloseBtn');

  // ==========================================
  // 1. FÍSICA TILT 3D INTERACTIVA
  // ==========================================
  const maxRotation = 13;

  card.addEventListener('mousemove', (e) => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;

    const rotX = -((y - centerY) / centerY) * maxRotation;
    const rotY = ((x - centerX) / centerX) * maxRotation;

    card.style.transform = `rotateX(${rotX.toFixed(2)}deg) rotateY(${rotY.toFixed(2)}deg) scale3d(1.02, 1.02, 1.02)`;

    glare.style.opacity = '1';
    glare.style.transform = `translate(${x - rect.width}px, ${y - rect.height}px)`;
  });

  card.addEventListener('mouseleave', () => {
    card.style.transform = 'rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
    card.style.transition = 'transform 0.6s cubic-bezier(0.23, 1, 0.32, 1)';
    glare.style.opacity = '0';
  });

  card.addEventListener('mouseenter', () => {
    card.style.transition = 'none';
  });

  // ==========================================
  // 2. PROCESAMIENTO Y EVALUACIÓN DE SOLICITUD
  // ==========================================
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Activar estado de carga en el botón
    btnEnviar.classList.add('loading');
    btnEnviar.disabled = true;

    const formData = new FormData(form);

    try {
      const response = await fetch('enviar.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      // Mostrar APROBADO o DESAPROBADO según respuesta
      if (data.status === 'aprobado') {
        showModal(
          true,
          'SOLICITUD APROBADA',
          '¡Despacho Autorizado!',
          data.message || 'La solicitud cumplió todos los requisitos y fue enviada con éxito a tu bandeja de correo.'
        );
        form.reset();
      } else {
        showModal(
          false,
          'SOLICITUD DESAPROBADA',
          'Envío Rechazado',
          data.message || 'La solicitud no pudo ser aprobada para su despacho.'
        );
      }
    } catch (error) {
      showModal(
        false,
        'SOLICITUD DESAPROBADA',
        'Error del Servidor',
        'No se pudo conectar con el servidor local para aprobar el envío.'
      );
    } finally {
      btnEnviar.classList.remove('loading');
      btnEnviar.disabled = false;
    }
  });

  function showModal(isApproved, veredicto, title, message) {
    popup.className = `status-popup ${isApproved ? 'aprobado' : 'desaprobado'}`;
    popupVeredicto.textContent = veredicto;
    popupTitle.textContent = title;
    popupMessage.textContent = message;

    if (isApproved) {
      popupIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
    } else {
      popupIcon.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
    }

    popup.classList.add('active');
  }

  popupCloseBtn.addEventListener('click', () => {
    popup.classList.remove('active');
  });
});