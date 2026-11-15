// Script principal del sistema administrativo

document.addEventListener('DOMContentLoaded', () => {
    const baseUrl = document.body.getAttribute('data-base-url') || '';

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatearFechaHoraActual() {
        const now = new Date();
        const dateStr = now.toLocaleDateString([], { day: '2-digit', month: '2-digit', year: '2-digit' });
        const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
        return `${dateStr}, ${timeStr}`;
    }

    // Desactivar autocompletado en formularios internos (excepto login)
    document.querySelectorAll('form:not([action*="/login"])').forEach((form) => {
        form.setAttribute('autocomplete', 'off');
    });
    document.querySelectorAll('input:not([type="hidden"])').forEach((inp) => {
        if (!inp.closest('form[action*="/login"]')) {
            inp.setAttribute('autocomplete', 'off');
        }
    });

    // Cuenta regresiva de 5 segundos para confirmar acciones delicadas
    function configurarTemporizadorConfirmacion(modalEl, btnEl, textoConfirmarHtml) {
        if (!modalEl || !btnEl) return;
        let timer = null;

        modalEl.addEventListener('shown.bs.modal', () => {
            if (timer) clearInterval(timer);
            btnEl.disabled = true;
            let secondsLeft = 5;
            btnEl.innerHTML = `<i class="bi bi-hourglass-split me-1"></i> Espere (${secondsLeft}s)`;

            timer = setInterval(() => {
                secondsLeft--;
                if (secondsLeft > 0) {
                    btnEl.innerHTML = `<i class="bi bi-hourglass-split me-1"></i> Espere (${secondsLeft}s)`;
                } else {
                    clearInterval(timer);
                    timer = null;
                    btnEl.disabled = false;
                    btnEl.innerHTML = textoConfirmarHtml;
                }
            }, 1000);
        });

        modalEl.addEventListener('hidden.bs.modal', () => {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
            btnEl.disabled = true;
            btnEl.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Espere (5s)';
        });
    }

    function mostrarModalZoom(modalId, imgId, titleId, url, titulo) {
        const modalEl = document.getElementById(modalId);
        const imgEl = document.getElementById(imgId);
        const titleEl = document.getElementById(titleId);
        if (modalEl && imgEl && titleEl) {
            imgEl.src = url;
            titleEl.textContent = titulo || 'Visualizador de Documento';
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // Avanzar entre campos del formulario al presionar Enter
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter') return;

        const target = e.target;
        if (!target || !target.matches('input, select, textarea')) return;

        const type = (target.type || '').toLowerCase();
        if (['button', 'submit', 'reset', 'file', 'hidden'].includes(type)) return;
        if (target.tagName === 'TEXTAREA' && e.shiftKey) return;

        const form = target.closest('form');
        if (!form || (form.id === 'formBuscarActas' && target.id === 'inputBusquedaActa')) return;

        const candidates = Array.from(form.querySelectorAll('input, select, textarea')).filter((el) => {
            if (el.disabled || el.readOnly) return false;
            const elType = (el.type || '').toLowerCase();
            if (['hidden', 'file', 'button', 'submit', 'reset'].includes(elType)) return false;
            if (el.classList.contains('d-none') || el.closest('.d-none')) return false;
            return el.offsetParent !== null;
        });

        const currentIndex = candidates.indexOf(target);
        if (currentIndex === -1) return;

        if (currentIndex < candidates.length - 1) {
            e.preventDefault();
            const nextField = candidates[currentIndex + 1];
            nextField.focus();
            if (typeof nextField.select === 'function' && nextField.tagName === 'INPUT') {
                const nextType = (nextField.type || '').toLowerCase();
                if (['text', 'number', 'password', 'email', 'tel', 'search'].includes(nextType)) {
                    nextField.select();
                }
            }
            return;
        }

        if (form.checkValidity()) {
            if (target.tagName === 'TEXTAREA' || target.tagName === 'SELECT') {
                e.preventDefault();
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        } else {
            e.preventDefault();
            form.reportValidity();
        }
    });

    /* ======================================================================
     * Modulo de Bitacora de Auditoria y Gestion de Usuarios
     * ====================================================================== */

    const formFiltrosLogs = document.getElementById('formFiltrosLogs');
    if (formFiltrosLogs) {
        const selPeriodo = document.getElementById('filtroPeriodo');
        const selOrden = document.getElementById('filtroOrden');
        const tbodyLogs = document.getElementById('tbodyLogs');

        /* Filtro por ajax para no recargar toda la tabal */
        function actualizarAuditoriaAjax() {
            const periodo = selPeriodo ? selPeriodo.value : 'hoy';
            const orden = selOrden ? selOrden.value : 'desc';
            const urlVista = baseUrl + '/auditoria?periodo=' + encodeURIComponent(periodo) + '&orden=' + encodeURIComponent(orden);
            const urlAjax = urlVista + '&ajax=1';

            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, '', urlVista);
            }

            fetch(urlAjax, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (!data || !data.ok || !tbodyLogs) {
                    formFiltrosLogs.submit();
                    return;
                }
                const logs = Array.isArray(data.logs) ? data.logs : [];
                if (logs.length === 0) {
                    tbodyLogs.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">No hay registros de actividad en el período seleccionado.</td></tr>';
                    return;
                }
                tbodyLogs.innerHTML = logs.map(function (log) {
                    const nombreCompleto = escapeHtml(log.nombres || 'Sistema');
                    return '<tr>' +
                        '<td class="text-secondary small text-nowrap">' + escapeHtml(log.fecha || '') + '</td>' +
                        '<td><div class="fw-semibold text-dark text-truncate audit-user-truncate" title="' + nombreCompleto + '">' + nombreCompleto + '</div></td>' +
                        '<td class="text-break">' + escapeHtml(log.accion || '') + '</td>' +
                        '<td><code class="text-muted small">' + escapeHtml(log.ip || '') + '</code></td>' +
                    '</tr>';
                }).join('');
            })
            .catch(function () {
                formFiltrosLogs.submit();
            });
        }

        if (selPeriodo) selPeriodo.addEventListener('change', actualizarAuditoriaAjax);
        if (selOrden) selOrden.addEventListener('change', actualizarAuditoriaAjax);
    }

    const moduloUsuarios = document.getElementById('moduloUsuariosContainer');
    if (moduloUsuarios) {
        const currentRol = moduloUsuarios.getAttribute('data-current-rol') || 'regular';
        const modalEditarUsuarioEl = document.getElementById('modalEditarUsuario');
        const modalEliminarUsuarioEl = document.getElementById('modalEliminarUsuario');

        if (modalEditarUsuarioEl) {
            const editModal = new bootstrap.Modal(modalEditarUsuarioEl);
            const editUserId = document.getElementById('editUserId');
            const editNacionalidad = document.getElementById('editNacionalidad');
            const editCedula = document.getElementById('editCedula');
            const editNombres = document.getElementById('editNombres');
            const editRolSelect = document.getElementById('editRolSelect');
            const editRolFixedDisplay = document.getElementById('editRolFixedDisplay');
            const editRolHelpText = document.getElementById('editRolHelpText');

            document.querySelectorAll('.btn-editar-usuario').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const rol = this.getAttribute('data-rol') || 'regular';

                    editUserId.value = this.getAttribute('data-id') || '';
                    editNacionalidad.value = this.getAttribute('data-nacionalidad') || 'V';
                    editCedula.value = this.getAttribute('data-cedula') || '';
                    editNombres.value = this.getAttribute('data-nombres') || '';

                    if (rol === 'admin_mayor') {
                        editRolSelect.classList.add('d-none');
                        editRolSelect.setAttribute('disabled', 'disabled');
                        editRolFixedDisplay.classList.remove('d-none');
                        editRolFixedDisplay.value = 'Admin Mayor';
                        editRolHelpText.textContent = 'El rol de Admin Mayor no se puede modificar.';
                    } else if (currentRol === 'admin') {
                        editRolSelect.classList.add('d-none');
                        editRolSelect.setAttribute('disabled', 'disabled');
                        editRolFixedDisplay.classList.remove('d-none');
                        editRolFixedDisplay.value = 'Regular';
                        editRolHelpText.textContent = 'Los administradores solo pueden gestionar usuarios con rol Regular.';
                    } else {
                        editRolFixedDisplay.classList.add('d-none');
                        editRolSelect.classList.remove('d-none');
                        editRolSelect.removeAttribute('disabled');
                        editRolSelect.value = rol === 'admin' ? 'admin' : 'regular';
                        editRolHelpText.textContent = 'Seleccione el rol correspondiente para este usuario.';
                    }

                    editModal.show();
                });
            });
        }

        if (modalEliminarUsuarioEl) {
            const deleteModal = new bootstrap.Modal(modalEliminarUsuarioEl);
            const deleteUserId = document.getElementById('deleteUserId');
            const deleteUserNombreText = document.getElementById('deleteUserNombreText');
            const btnConfirmDeleteUser = document.getElementById('btnConfirmDeleteUser');

            document.querySelectorAll('.btn-eliminar-usuario').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id') || '';
                    const nombre = this.getAttribute('data-nombre') || '';
                    const cedula = this.getAttribute('data-cedula') || '';

                    deleteUserId.value = id;
                    deleteUserNombreText.textContent = nombre + ' (' + cedula + ')';
                    deleteModal.show();
                });
            });

            // Temporisador de 5 seg para confirmar eliminacion
            configurarTemporizadorConfirmacion(
                modalEliminarUsuarioEl,
                btnConfirmDeleteUser,
                '<i class="bi bi-trash3 me-1"></i> Confirmar Eliminación'
            );
        }
    }

    // ----------------------------------------------------------------------
    // modulo de personal y expedientes
    // ----------------------------------------------------------------------

    // popovers de condicion medica
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach((el) => {
        new bootstrap.Popover(el, {
            trigger: 'hover focus',
            container: 'body',
            html: true
        });
    });

    // vista previa de imagenes y archivos pdf al subirlos
    function setupImagePreview(inputId, imgId, placeholderId, containerId, pdfId) {
        const input = document.getElementById(inputId);
        if (!input) return;

        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            const img = document.getElementById(imgId);
            const placeholder = placeholderId ? document.getElementById(placeholderId) : null;
            const container = containerId ? document.getElementById(containerId) : null;
            const pdfNotice = pdfId ? document.getElementById(pdfId) : null;

            if (!file) return;

            if (file.type === 'application/pdf') {
                if (img) img.classList.add('d-none');
                if (placeholder) placeholder.classList.add('d-none');
                if (pdfNotice) pdfNotice.classList.remove('d-none');
                if (container) container.classList.remove('d-none');
            } else if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (evt) => {
                    if (img) {
                        img.src = evt.target.result;
                        img.classList.remove('d-none');
                    }
                    if (placeholder) placeholder.classList.add('d-none');
                    if (pdfNotice) pdfNotice.classList.add('d-none');
                    if (container) container.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    setupImagePreview('inputFotoCrear', 'previewFotoCrear', 'placeholderFotoCrear');
    setupImagePreview('inputFotoEditar', 'previewFotoEditar', 'placeholderFotoEditar');
    setupImagePreview('inputArchivoExpCrear', 'previewImgExpCrear', null, 'previewContainerExpCrear', 'previewPdfExpCrear');
    setupImagePreview('inputArchivoExpEditar', 'previewImgExpEditar', null, 'previewContainerExpEditar', 'previewPdfExpEditar');

    // contadores de caracteres para titulo y descripcion del expediente
    function setupCharCounter(inputId, counterId, maxLimit) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(counterId);
        if (!input || !counter) return;

        const updateCounter = () => {
            const len = input.value.length;
            counter.textContent = len;
            counter.classList.toggle('text-danger', len >= maxLimit);
            counter.classList.toggle('text-primary', len < maxLimit);
        };

        input.addEventListener('input', updateCounter);
        input.addEventListener('keyup', updateCounter);
        updateCounter();
    }

    setupCharCounter('inputExpDescripcionCrear', 'charCountExpCrear', 100);
    setupCharCounter('editExpDescripcion', 'charCountExpEditar', 100);
    setupCharCounter('inputExpTituloCrear', 'charCountTituloCrear', 30);
    setupCharCounter('editExpTitulo', 'charCountTituloEditar', 30);

    // habilitar o bloquear el campo de detalle de enfermedad
    function setupEnfermedadToggle(selectId, inputId) {
        const select = document.getElementById(selectId);
        const input = document.getElementById(inputId);
        if (!select || !input) return;

        select.addEventListener('change', function () {
            if (this.value === 'Si') {
                input.removeAttribute('disabled');
                input.focus();
                input.setAttribute('required', 'required');
            } else {
                input.setAttribute('disabled', 'disabled');
                input.removeAttribute('required');
                input.value = '';
            }
        });
    }

    setupEnfermedadToggle('padeceSelectCrear', 'detalleInputCrear');
    setupEnfermedadToggle('padeceSelectEditar', 'detalleInputEditar');

    // buscador en vivo, filtros y scroll infinito de la lista de personal
    const liveSearch = document.getElementById('liveSearchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const btnResetSearch = document.getElementById('btnResetSearch');
    const cardsPersonal = Array.from(document.querySelectorAll('.personal-card-item'));
    const noResultsPersonal = document.getElementById('noResultsNotice');
    const totalBadgePersonal = document.getElementById('totalPersonalBadge');
    const estadoCargandoPersonal = document.getElementById('estadoCargandoPersonal');
    const spinnerScrollPersonal = document.getElementById('spinnerScrollPersonal');
    const cardsContainerPersonal = document.getElementById('personalCardsContainer');

    if (cardsPersonal.length > 0 || liveSearch) {
        const LIMITE_INICIAL_PERSONAL = 10;
        const BATCH_SCROLL_PERSONAL = 5;
        let matchingCardsPersonal = [];
        let itemsRenderizadosPersonal = 0;
        let cargandoBatchPersonal = false;
        let searchDebounceTimeout = null;
        let loadingTimerPersonal = null;

        let currentFilters = {
            cargo: '',
            sexo: '',
            estadoCivil: '',
            enfermedad: '',
            anioNac: '',
            estadoPersonal: 'activo'
        };

        function normalizarTextoBusqueda(str) {
            if (!str) return '';
            return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
        }

        function cargarSiguienteLotePersonal() {
            if (cargandoBatchPersonal) return;
            const total = matchingCardsPersonal.length;
            if (itemsRenderizadosPersonal >= total) {
                if (spinnerScrollPersonal) spinnerScrollPersonal.classList.add('d-none');
                return;
            }

            if (itemsRenderizadosPersonal === 0) {
                const limiteSiguiente = Math.min(LIMITE_INICIAL_PERSONAL, total);
                for (let i = 0; i < limiteSiguiente; i++) {
                    matchingCardsPersonal[i].classList.remove('d-none');
                }
                itemsRenderizadosPersonal = limiteSiguiente;
                if (spinnerScrollPersonal) spinnerScrollPersonal.classList.add('d-none');
                return;
            }

            cargandoBatchPersonal = true;
            if (spinnerScrollPersonal) spinnerScrollPersonal.classList.remove('d-none');

            setTimeout(() => {
                const limiteSiguiente = Math.min(itemsRenderizadosPersonal + BATCH_SCROLL_PERSONAL, total);
                for (let i = itemsRenderizadosPersonal; i < limiteSiguiente; i++) {
                    matchingCardsPersonal[i].classList.remove('d-none');
                }
                itemsRenderizadosPersonal = limiteSiguiente;

                if (spinnerScrollPersonal) spinnerScrollPersonal.classList.add('d-none');
                cargandoBatchPersonal = false;

                if (itemsRenderizadosPersonal < total && (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 100)) {
                    setTimeout(cargarSiguienteLotePersonal, 50);
                }
            }, 150);
        }

        function applyFilterAndSearch() {
            if (!cardsPersonal.length) return;

            const rawTerm = liveSearch ? liveSearch.value : '';
            const term = normalizarTextoBusqueda(rawTerm);
            matchingCardsPersonal = [];

            cardsPersonal.forEach((card) => {
                const nombre = card.getAttribute('data-nombre') || '';
                const cedula = card.getAttribute('data-cedula') || '';
                const cargo = card.getAttribute('data-cargo') || '';
                const sexo = card.getAttribute('data-sexo') || '';
                const estadoCivil = card.getAttribute('data-estado-civil') || '';
                const enfermedad = card.getAttribute('data-enfermedad') || '';
                const fechaNac = card.getAttribute('data-fecha-nac') || '';
                const anioNac = fechaNac ? fechaNac.substring(0, 4) : '';
                const activoAttr = card.getAttribute('data-activo') || '1';

                const matchesSearch = !term || normalizarTextoBusqueda(nombre).includes(term) || cedula.includes(term);

                let matchesEstado = true;
                if (currentFilters.estadoPersonal === 'activo') {
                    matchesEstado = (activoAttr === '1');
                } else if (currentFilters.estadoPersonal === 'deshabilitado') {
                    matchesEstado = (activoAttr === '0');
                }

                const matchesCargo = !currentFilters.cargo || cargo === currentFilters.cargo;
                const matchesSexo = !currentFilters.sexo || sexo === currentFilters.sexo;
                const matchesEstadoCivil = !currentFilters.estadoCivil || estadoCivil === currentFilters.estadoCivil;
                const matchesEnfermedad = !currentFilters.enfermedad || enfermedad === currentFilters.enfermedad;
                const matchesAnioNac = !currentFilters.anioNac || anioNac === currentFilters.anioNac;

                if (matchesSearch && matchesEstado && matchesCargo && matchesSexo && matchesEstadoCivil && matchesEnfermedad && matchesAnioNac) {
                    matchingCardsPersonal.push(card);
                }
            });

            if (totalBadgePersonal) totalBadgePersonal.textContent = matchingCardsPersonal.length;

            if (matchingCardsPersonal.length === 0) {
                cardsPersonal.forEach((card) => card.classList.add('d-none'));
                if (cardsContainerPersonal) cardsContainerPersonal.classList.add('d-none');
                if (noResultsPersonal) noResultsPersonal.classList.remove('d-none');
                if (spinnerScrollPersonal) spinnerScrollPersonal.classList.add('d-none');
                itemsRenderizadosPersonal = 0;
            } else {
                if (cardsContainerPersonal) cardsContainerPersonal.classList.remove('d-none');
                if (noResultsPersonal) noResultsPersonal.classList.add('d-none');
                if (spinnerScrollPersonal) spinnerScrollPersonal.classList.add('d-none');

                const matchingSet = new Set(matchingCardsPersonal.slice(0, LIMITE_INICIAL_PERSONAL));
                cardsPersonal.forEach((card) => {
                    if (matchingSet.has(card)) {
                        card.classList.remove('d-none');
                    } else {
                        card.classList.add('d-none');
                    }
                });
                itemsRenderizadosPersonal = Math.min(LIMITE_INICIAL_PERSONAL, matchingCardsPersonal.length);
            }
        }

        function ejecutarBusquedaConCarga(inmediato = false) {
            if (searchDebounceTimeout) clearTimeout(searchDebounceTimeout);
            if (loadingTimerPersonal) clearTimeout(loadingTimerPersonal);

            loadingTimerPersonal = setTimeout(() => {
                if (estadoCargandoPersonal) {
                    estadoCargandoPersonal.classList.remove('d-none');
                    if (cardsContainerPersonal) cardsContainerPersonal.classList.add('d-none');
                    if (noResultsPersonal) noResultsPersonal.classList.add('d-none');
                }
            }, 1000);

            const delay = inmediato ? 0 : 120;

            searchDebounceTimeout = setTimeout(() => {
                if (loadingTimerPersonal) {
                    clearTimeout(loadingTimerPersonal);
                    loadingTimerPersonal = null;
                }
                if (estadoCargandoPersonal) estadoCargandoPersonal.classList.add('d-none');
                applyFilterAndSearch();
            }, delay);
        }

        let scrollTimeoutPersonal = null;
        window.addEventListener('scroll', () => {
            if (scrollTimeoutPersonal) return;
            scrollTimeoutPersonal = setTimeout(() => {
                scrollTimeoutPersonal = null;
                if (matchingCardsPersonal.length === 0 || itemsRenderizadosPersonal >= matchingCardsPersonal.length) return;
                if ((window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 350)) {
                    cargarSiguienteLotePersonal();
                }
            }, 80);
        }, { passive: true });

        if (liveSearch) {
            liveSearch.addEventListener('input', () => ejecutarBusquedaConCarga(false));
        }

        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', () => {
                if (liveSearch) {
                    liveSearch.value = '';
                    if (searchDebounceTimeout) clearTimeout(searchDebounceTimeout);
                    if (loadingTimerPersonal) clearTimeout(loadingTimerPersonal);
                    if (estadoCargandoPersonal) estadoCargandoPersonal.classList.add('d-none');
                    applyFilterAndSearch();
                    liveSearch.focus();
                }
            });
        }

        const btnModalAplicar = document.getElementById('btnModalAplicarFiltros');
        const btnModalReset = document.getElementById('btnModalResetFiltros');
        const btnResetFilters = document.getElementById('btnResetFilters');
        const filtrosBadge = document.getElementById('filtrosActivosBadge');
        const activeFiltersNotice = document.getElementById('activeFiltersNotice');
        const activeFiltersSummary = document.getElementById('activeFiltersSummary');

        function resetModalFilters() {
            currentFilters = { cargo: '', sexo: '', estadoCivil: '', enfermedad: '', anioNac: '', estadoPersonal: 'activo' };
            if (document.getElementById('filtroCargo')) document.getElementById('filtroCargo').value = '';
            if (document.getElementById('filtroSexo')) document.getElementById('filtroSexo').value = '';
            if (document.getElementById('filtroEstadoCivil')) document.getElementById('filtroEstadoCivil').value = '';
            if (document.getElementById('filtroEnfermedad')) document.getElementById('filtroEnfermedad').value = '';
            if (document.getElementById('filtroAnioNac')) document.getElementById('filtroAnioNac').value = '';
            if (document.getElementById('filtroEstadoPersonal')) document.getElementById('filtroEstadoPersonal').value = 'activo';

            if (filtrosBadge) filtrosBadge.classList.add('d-none');
            if (activeFiltersNotice) activeFiltersNotice.classList.add('d-none');
            if (searchDebounceTimeout) clearTimeout(searchDebounceTimeout);
            if (loadingTimerPersonal) clearTimeout(loadingTimerPersonal);
            if (estadoCargandoPersonal) estadoCargandoPersonal.classList.add('d-none');
            applyFilterAndSearch();
        }

        if (btnResetSearch) {
            btnResetSearch.addEventListener('click', () => {
                if (liveSearch) liveSearch.value = '';
                resetModalFilters();
            });
        }

        if (btnModalReset) btnModalReset.addEventListener('click', resetModalFilters);
        if (btnResetFilters) btnResetFilters.addEventListener('click', resetModalFilters);

        if (btnModalAplicar) {
            btnModalAplicar.addEventListener('click', () => {
                currentFilters.cargo = (document.getElementById('filtroCargo')?.value || '').toLowerCase();
                currentFilters.sexo = document.getElementById('filtroSexo')?.value || '';
                currentFilters.estadoCivil = (document.getElementById('filtroEstadoCivil')?.value || '').toLowerCase();
                currentFilters.enfermedad = document.getElementById('filtroEnfermedad')?.value || '';
                currentFilters.anioNac = (document.getElementById('filtroAnioNac')?.value || '').trim();
                currentFilters.estadoPersonal = document.getElementById('filtroEstadoPersonal') ? document.getElementById('filtroEstadoPersonal').value : 'activo';

                let activeCount = 0;
                const summaryParts = [];
                if (currentFilters.estadoPersonal !== 'activo') {
                    activeCount++;
                    summaryParts.push('Estado: ' + (currentFilters.estadoPersonal === 'deshabilitado' ? 'Deshabilitados' : 'Todos'));
                }
                if (currentFilters.cargo) { activeCount++; summaryParts.push('Cargo: ' + currentFilters.cargo); }
                if (currentFilters.sexo) { activeCount++; summaryParts.push('Sexo: ' + currentFilters.sexo); }
                if (currentFilters.estadoCivil) { activeCount++; summaryParts.push('E. Civil: ' + currentFilters.estadoCivil); }
                if (currentFilters.enfermedad) { activeCount++; summaryParts.push('Enfermedad: ' + currentFilters.enfermedad); }
                if (currentFilters.anioNac) { activeCount++; summaryParts.push('Año Nac: ' + currentFilters.anioNac); }

                if (activeCount > 0) {
                    if (filtrosBadge) {
                        filtrosBadge.textContent = activeCount;
                        filtrosBadge.classList.remove('d-none');
                    }
                    if (activeFiltersNotice && activeFiltersSummary) {
                        activeFiltersSummary.textContent = summaryParts.join(' | ');
                        activeFiltersNotice.classList.remove('d-none');
                    }
                } else {
                    if (filtrosBadge) filtrosBadge.classList.add('d-none');
                    if (activeFiltersNotice) activeFiltersNotice.classList.add('d-none');
                }

                ejecutarBusquedaConCarga(true);
            });
        }

        applyFilterAndSearch();
    }

    // habre el modal de edicion con los datos cargados
    document.querySelectorAll('.btn-editar-personal').forEach((btn) => {
        btn.addEventListener('click', function () {
            const d = this.dataset;
            document.getElementById('editPersonalId').value = d.id || '';
            document.getElementById('editPersonalRedirectTo').value = d.origin || 'perfil';
            document.getElementById('editPersonalNombres').value = d.nombres || '';
            document.getElementById('editPersonalApellidos').value = d.apellidos || '';
            document.getElementById('editPersonalNacionalidad').value = d.nacionalidad || 'V';
            document.getElementById('editPersonalCedula').value = d.cedula || '';
            document.getElementById('editPersonalFechaNac').value = d.fechaNac || '';
            document.getElementById('editPersonalCargo').value = d.cargo || 'Docente';
            document.getElementById('editPersonalTelefono').value = d.telefono || '';
            document.getElementById('editPersonalCorreo').value = d.correo || '';
            document.getElementById('editPersonalSexo').value = d.sexo || 'F';
            document.getElementById('editPersonalEstadoCivil').value = d.estadoCivil || 'Soltero/a';
            document.getElementById('editPersonalDireccion').value = d.direccion || '';

            const enfermedad = d.enfermedad || 'No';
            document.getElementById('padeceSelectEditar').value = enfermedad;
            const detalleInput = document.getElementById('detalleInputEditar');
            detalleInput.value = d.detalleEnfermedad || '';

            if (enfermedad === 'Si') {
                detalleInput.removeAttribute('disabled');
                detalleInput.setAttribute('required', 'required');
            } else {
                detalleInput.setAttribute('disabled', 'disabled');
                detalleInput.removeAttribute('required');
            }

            const previewImg = document.getElementById('previewFotoEditar');
            const placeholder = document.getElementById('placeholderFotoEditar');
            const foto = d.foto || '';
            previewImg.src = foto;
            previewImg.classList.toggle('d-none', !foto);
            if (placeholder) placeholder.classList.toggle('d-none', Boolean(foto));

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarPersonal')).show();
        });
    });

    // modales para deshabilitar y habilitar personal
    const modalDeshabilitarPersonalEl = document.getElementById('modalDeshabilitarPersonal');
    const btnConfirmDeshabilitarPersonal = document.getElementById('btnConfirmDeshabilitarPersonal');

    document.querySelectorAll('.btn-deshabilitar-personal').forEach((btn) => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id') || '';
            const nombre = this.getAttribute('data-nombre') || '';
            const cedula = this.getAttribute('data-cedula') || '';

            document.getElementById('deshabilitarPersonalId').value = id;
            document.getElementById('deshabilitarPersonalNombreText').textContent = nombre + (cedula ? ` (Cédula: ${cedula})` : '');

            const modal = bootstrap.Modal.getOrCreateInstance(modalDeshabilitarPersonalEl);
            modal.show();
        });
    });

    configurarTemporizadorConfirmacion(
        modalDeshabilitarPersonalEl,
        btnConfirmDeshabilitarPersonal,
        '<i class="bi bi-person-slash me-1"></i> Sí, Deshabilitar Personal'
    );

    const modalHabilitarPersonalEl = document.getElementById('modalHabilitarPersonal');
    document.querySelectorAll('.btn-habilitar-personal').forEach((btn) => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id') || '';
            const nombre = this.getAttribute('data-nombre') || '';
            const cedula = this.getAttribute('data-cedula') || '';
            const redirect = this.getAttribute('data-redirect') || 'lista';

            document.getElementById('habilitarPersonalId').value = id;
            document.getElementById('habilitarPersonalNombreText').textContent = nombre + (cedula ? ` (Cédula: ${cedula})` : '');
            document.getElementById('habilitarRedirectTo').value = redirect;

            const modal = bootstrap.Modal.getOrCreateInstance(modalHabilitarPersonalEl);
            modal.show();
        });
    });

    // edicion y eliminacion de documentos del expediente
    document.querySelectorAll('.btn-editar-expediente').forEach((btn) => {
        btn.addEventListener('click', function () {
            const expId = this.getAttribute('data-id');
            const titulo = this.getAttribute('data-titulo') || '';
            const descripcion = this.getAttribute('data-descripcion') || '';
            const fechaVenc = this.getAttribute('data-fecha-vencimiento') || '';

            document.getElementById('editExpId').value = expId;
            document.getElementById('editExpTitulo').value = titulo;
            const counterTitulo = document.getElementById('charCountTituloEditar');
            if (counterTitulo) counterTitulo.textContent = titulo.length;

            const descArea = document.getElementById('editExpDescripcion');
            descArea.value = descripcion;
            const counter = document.getElementById('charCountExpEditar');
            if (counter) counter.textContent = descripcion.length;

            document.getElementById('editExpFechaVencimiento').value = fechaVenc;

            const previewContainer = document.getElementById('previewContainerExpEditar');
            if (previewContainer) previewContainer.classList.add('d-none');

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarExpediente')).show();
        });
    });

    const modalEliminarExpEl = document.getElementById('modalEliminarExpediente');
    const btnConfirmDeleteExp = document.getElementById('btnConfirmDeleteExp');

    document.querySelectorAll('.btn-eliminar-expediente').forEach((btn) => {
        btn.addEventListener('click', function () {
            const expId = this.getAttribute('data-id');
            const titulo = this.getAttribute('data-titulo') || '';

            document.getElementById('deleteExpId').value = expId;
            document.getElementById('deleteExpTituloText').textContent = `"${titulo}" (Registro #${expId})`;

            bootstrap.Modal.getOrCreateInstance(modalEliminarExpEl).show();
        });
    });

    configurarTemporizadorConfirmacion(
        modalEliminarExpEl,
        btnConfirmDeleteExp,
        '<i class="bi bi-trash3-fill me-1"></i> Eliminar Registro'
    );

    // zoom para las fotos y documentos del expediente
    document.querySelectorAll('.btn-zoom-imagen').forEach((img) => {
        img.addEventListener('click', function () {
            mostrarModalZoom(
                'modalZoomImg',
                'modalZoomSrc',
                'modalZoomTitulo',
                this.getAttribute('data-zoom-src') || '',
                this.getAttribute('data-zoom-title') || 'Visualizador de Documento'
            );
        });
    });

    // opciones de descarga e impresion directa del expediente
    const modalOpcionesDescargaEl = document.getElementById('modalOpcionesDescarga');
    if (modalOpcionesDescargaEl) {
        const modalOpcionesDescarga = new bootstrap.Modal(modalOpcionesDescargaEl);
        let selectedRecordData = null;

        document.querySelectorAll('.btn-opciones-descarga').forEach((btn) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const expId = this.getAttribute('data-id');
                const titulo = this.getAttribute('data-titulo') || 'Documento';

                selectedRecordData = {
                    id: expId,
                    titulo: titulo,
                    fileUrl: this.getAttribute('data-file-url') || '',
                    descripcion: this.getAttribute('data-descripcion') || '',
                    fechaRegistro: this.getAttribute('data-fecha-registro') || '',
                    fechaVencimiento: this.getAttribute('data-fecha-vencimiento') || ''
                };

                const tituloEl = document.getElementById('descargaTituloDoc');
                if (tituloEl) tituloEl.textContent = `"${titulo}"`;

                const btnOriginal = document.getElementById('btnDescargaOriginal');
                if (btnOriginal) {
                    btnOriginal.href = `${baseUrl}/personal/downloadFile?exp_id=${expId}&download=1`;
                    if (!btnOriginal.dataset.attachedClose) {
                        btnOriginal.dataset.attachedClose = '1';
                        btnOriginal.addEventListener('click', () => {
                            setTimeout(() => modalOpcionesDescarga.hide(), 150);
                        });
                    }
                }

                const btnPdf = document.getElementById('btnDescargaPdf');
                if (btnPdf) {
                    btnPdf.href = `${baseUrl}/personal/printExpediente?exp_id=${expId}`;
                    if (!btnPdf.dataset.attachedClose) {
                        btnPdf.dataset.attachedClose = '1';
                        btnPdf.addEventListener('click', () => {
                            setTimeout(() => modalOpcionesDescarga.hide(), 150);
                        });
                    }
                }

                modalOpcionesDescarga.show();
            });
        });

        const btnImprimir = document.getElementById('btnImprimirDoc');
        if (btnImprimir) {
            btnImprimir.addEventListener('click', (e) => {
                e.preventDefault();
                if (!selectedRecordData) return;

                const printEl = document.getElementById('documentoImpresionExpediente');
                if (printEl && printEl.parentElement !== document.body) {
                    document.body.appendChild(printEl);
                }

                const timeEl = document.getElementById('printHeaderTimestamp');
                if (timeEl) timeEl.textContent = formatearFechaHoraActual();

                const tituloEl = document.getElementById('printDocTitulo');
                if (tituloEl) tituloEl.textContent = selectedRecordData.titulo;

                const imgEl = document.getElementById('printDocImg');
                if (imgEl) imgEl.src = selectedRecordData.fileUrl;

                const descEl = document.getElementById('printDocDesc');
                if (descEl) descEl.textContent = selectedRecordData.descripcion;

                const fechaEl = document.getElementById('printDocFecha');
                if (fechaEl) fechaEl.textContent = selectedRecordData.fechaRegistro;

                const vencEl = document.getElementById('printDocVencimiento');
                const vencCont = document.getElementById('printDocVencimientoContainer');
                if (selectedRecordData.fechaVencimiento) {
                    if (vencEl) vencEl.textContent = selectedRecordData.fechaVencimiento;
                    if (vencCont) vencCont.classList.remove('d-none');
                } else {
                    if (vencEl) vencEl.textContent = '';
                    if (vencCont) vencCont.classList.add('d-none');
                }

                modalOpcionesDescarga.hide();

                // calculamos el espacio disponible para la imgen en la hoja
                function ejecutarImpresionNativa() {
                    const printSheet = document.getElementById('documentoImpresionExpediente');
                    if (printSheet) {
                        const img = printSheet.querySelector('.print-doc-main-img');
                        const imgContainer = printSheet.querySelector('.doc-img-container');
                        if (img && imgContainer) {
                            const header = printSheet.querySelector('.header-print');
                            const profile = printSheet.querySelector('.profile-box');
                            const title = printSheet.querySelector('.doc-title');
                            const desc = printSheet.querySelector('.doc-desc-box');
                            const footer = printSheet.querySelector('.footer-vencimiento');

                            const sheetInnerHeight = 980 - 40;
                            const hHeader = (header && header.offsetHeight > 0) ? header.offsetHeight + 10 : 34;
                            const hProfile = (profile && profile.offsetHeight > 0) ? profile.offsetHeight + 10 : 112;
                            const hTitle = (title && title.offsetHeight > 0) ? title.offsetHeight + 10 : 36;
                            const hDesc = (desc && desc.offsetHeight > 0) ? desc.offsetHeight + 10 : 75;
                            const hFooter = (footer && footer.offsetHeight > 0) ? footer.offsetHeight : 38;

                            const espOcupado = hHeader + hProfile + hTitle + hDesc + hFooter;
                            const maxImgH = Math.max(160, sheetInnerHeight - espOcupado - 16);

                            imgContainer.style.height = `${maxImgH}px`;
                            imgContainer.style.maxHeight = `${maxImgH}px`;
                            img.style.maxHeight = `${maxImgH - 16}px`;
                        }
                    }
                    document.body.classList.add('imprimiendo-expediente');
                    setTimeout(() => {
                        window.print();
                        window.addEventListener('afterprint', () => {
                            document.body.classList.remove('imprimiendo-expediente');
                        }, { once: true });
                        setTimeout(() => {
                            document.body.classList.remove('imprimiendo-expediente');
                        }, 2500);
                    }, 200);
                }

                if (imgEl && imgEl.complete && imgEl.naturalWidth !== 0) {
                    setTimeout(ejecutarImpresionNativa, 200);
                } else if (imgEl) {
                    imgEl.onload = () => setTimeout(ejecutarImpresionNativa, 150);
                    imgEl.onerror = () => setTimeout(ejecutarImpresionNativa, 150);
                    setTimeout(ejecutarImpresionNativa, 500);
                } else {
                    setTimeout(ejecutarImpresionNativa, 250);
                }
            });
        }
    }

    // Modulo de actas de graduacion

    function actualizarFechasEncabezadoActa() {
        const fechaHora = formatearFechaHoraActual();
        document.querySelectorAll('.acta-live-datetime').forEach((el) => {
            el.textContent = fechaHora;
        });
    }
    actualizarFechasEncabezadoActa();

    function crearHtmlFilaEstudiante(index) {
        const numStr = String(index + 1).padStart(2, '0');
        const tr = document.createElement('tr');
        tr.className = 'fila-estudiante-acta';
        tr.innerHTML = `
            <td class="text-center fw-bold text-muted celda-num-orden">${numStr}</td>
            <td>
                <input type="text" name="estudiantes[${index}][apellidos_nombres]" class="form-control form-control-sm inp-nombre" maxlength="120" required placeholder="Ej. Araujo R, Orlando J" autocomplete="off">
            </td>
            <td>
                <input type="text" name="estudiantes[${index}][cedula]" class="form-control form-control-sm text-center inp-cedula" maxlength="20" placeholder="Opcional" autocomplete="off">
            </td>
            <td>
                <input type="text" inputmode="numeric" name="estudiantes[${index}][dia]" class="form-control form-control-sm text-center inp-dia acta-no-spin" maxlength="2" pattern="\\d{2}" required placeholder="DD" autocomplete="off">
            </td>
            <td>
                <input type="text" inputmode="numeric" name="estudiantes[${index}][mes]" class="form-control form-control-sm text-center inp-mes acta-no-spin" maxlength="2" pattern="\\d{2}" required placeholder="MM" autocomplete="off">
            </td>
            <td>
                <input type="text" inputmode="numeric" name="estudiantes[${index}][anio]" class="form-control form-control-sm text-center inp-anio acta-no-spin" maxlength="4" pattern="\\d{2}|\\d{4}" required placeholder="AA" autocomplete="off">
            </td>
            <td>
                <input type="text" name="estudiantes[${index}][literal_nota]" class="form-control form-control-sm text-center fw-bold inp-literal" maxlength="10" required placeholder="A / 20" autocomplete="off">
            </td>
            <td>
                <input type="text" name="estudiantes[${index}][observacion]" class="form-control form-control-sm inp-obs" maxlength="25" placeholder="Opcional" autocomplete="off">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1.5 btn-quitar-fila-acta" title="Quitar estudiante" aria-label="Quitar estudiante">
                    <i class="bi bi-x-lg"></i>
                </button>
            </td>
        `;
        return tr;
    }

    function capitalizarNombre(str) {
        if (!str) return '';
        return str.replace(/(^|[\s,.\-'])([\p{L}])/gu, (match, sep, char) => sep + char.toUpperCase());
    }

    function configurarFormateoDosDigitos(tbody) {
        if (!tbody) return;

        tbody.addEventListener('input', (e) => {
            if (e.target.matches('.inp-dia, .inp-mes')) {
                e.target.value = e.target.value.replace(/\D/g, '').slice(0, 2);
            } else if (e.target.matches('.inp-anio')) {
                e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
            } else if (e.target.matches('.inp-nombre')) {
                const inp = e.target;
                const start = inp.selectionStart;
                const end = inp.selectionEnd;
                const orig = inp.value;
                const cap = capitalizarNombre(orig);
                if (orig !== cap) {
                    inp.value = cap;
                    inp.setSelectionRange(start, end);
                }
            }
        });

        tbody.addEventListener('blur', (e) => {
            const inp = e.target;
            if (inp.matches('.inp-nombre')) {
                inp.value = capitalizarNombre(inp.value.trim());
                return;
            }
            if (!inp.matches('.inp-dia, .inp-mes, .inp-anio')) return;
            const val = inp.value.trim();
            if (!val) return;

            let num = parseInt(val, 10);
            if (isNaN(num)) {
                inp.value = '';
                return;
            }

            if (inp.classList.contains('inp-dia')) {
                if (num < 1) num = 1;
                if (num > 31) num = 31;
                inp.value = String(num).padStart(2, '0');
            } else if (inp.classList.contains('inp-mes')) {
                if (num < 1) num = 1;
                if (num > 12) num = 12;
                inp.value = String(num).padStart(2, '0');
            } else if (inp.classList.contains('inp-anio')) {
                if (val.length <= 2) {
                    inp.value = String(num).padStart(2, '0');
                } else {
                    inp.value = String(num).padStart(4, '0');
                }
            }
        }, true);
    }

    function reindexarTablaEstudiantes(tbody) {
        if (!tbody) return;
        tbody.querySelectorAll('tr.fila-estudiante-acta').forEach((tr, idx) => {
            const celdaNum = tr.querySelector('.celda-num-orden');
            if (celdaNum) celdaNum.textContent = String(idx + 1).padStart(2, '0');
            tr.querySelectorAll('input[name^="estudiantes["]').forEach((inp) => {
                inp.name = inp.name.replace(/^estudiantes\[\d+\]/, `estudiantes[${idx}]`);
            });
        });
    }

    function agregarFilaEstudiante(tbody) {
        if (!tbody) return;
        const currentCount = tbody.querySelectorAll('tr.fila-estudiante-acta').length;
        const nuevaFila = crearHtmlFilaEstudiante(currentCount);
        tbody.appendChild(nuevaFila);
        const primerInput = nuevaFila.querySelector('.inp-nombre');
        if (primerInput) primerInput.focus();
    }

    const tbodyCrear = document.getElementById('tbodyEstudiantesCrear');
    if (tbodyCrear && tbodyCrear.children.length === 0) {
        for (let i = 0; i < 5; i++) {
            tbodyCrear.appendChild(crearHtmlFilaEstudiante(i));
        }
    }
    configurarFormateoDosDigitos(tbodyCrear);

    ['btnAgregarFilaCrear', 'btnAgregarFilaCrearBottom'].forEach((id) => {
        const btn = document.getElementById(id);
        if (btn) btn.addEventListener('click', () => agregarFilaEstudiante(tbodyCrear));
    });

    const tbodyEditar = document.getElementById('tbodyEstudiantesEditar');
    configurarFormateoDosDigitos(tbodyEditar);
    ['btnAgregarFilaEditar', 'btnAgregarFilaEditarBottom'].forEach((id) => {
        const btn = document.getElementById(id);
        if (btn) btn.addEventListener('click', () => agregarFilaEstudiante(tbodyEditar));
    });

    [tbodyCrear, tbodyEditar].forEach((tbody) => {
        if (!tbody) return;
        tbody.addEventListener('click', (e) => {
            const btnQuitar = e.target.closest('.btn-quitar-fila-acta');
            if (!btnQuitar) return;
            const filas = tbody.querySelectorAll('tr.fila-estudiante-acta');
            if (filas.length <= 1) {
                alert('El acta debe tener al menos un estudiante.');
                return;
            }
            const fila = btnQuitar.closest('tr.fila-estudiante-acta');
            if (fila) {
                fila.remove();
                reindexarTablaEstudiantes(tbody);
            }
        });
    });

    function construirVistaPrevia(tbodyOrigen, tbodyDestino, inputAnio, labelAnioDestino) {
        if (!tbodyOrigen || !tbodyDestino) return;
        const anioTxt = (inputAnio && inputAnio.value.trim() !== '') ? inputAnio.value.trim() : '----';
        if (labelAnioDestino) {
            labelAnioDestino.textContent = `Año Escolar ${anioTxt}`;
        }

        const filas = tbodyOrigen.querySelectorAll('tr.fila-estudiante-acta');
        let contador = 0;
        let htmlRows = '';

        filas.forEach((tr) => {
            const nombre = (tr.querySelector('.inp-nombre')?.value || '').trim();
            const cedula = (tr.querySelector('.inp-cedula')?.value || '').trim();
            const diaRaw = (tr.querySelector('.inp-dia')?.value || '').trim();
            const mesRaw = (tr.querySelector('.inp-mes')?.value || '').trim();
            const anioRaw = (tr.querySelector('.inp-anio')?.value || '').trim();
            const literal = (tr.querySelector('.inp-literal')?.value || '').trim();
            const obs = (tr.querySelector('.inp-obs')?.value || '').trim();

            if (!nombre && !cedula && !literal) return;
            contador++;

            const diaFmt = diaRaw ? diaRaw.padStart(2, '0') : '';
            const mesFmt = mesRaw ? mesRaw.padStart(2, '0') : '';
            const anioFmt = (anioRaw.length === 4) ? anioRaw : (anioRaw.length >= 2 ? anioRaw.slice(-2).padStart(2, '0') : (anioRaw ? anioRaw.padStart(2, '0') : ''));
            const numFmt = String(contador).padStart(2, '0');

            htmlRows += `<tr>
                <td class="text-center">${numFmt}</td>
                <td class="acta-cell-nombre">${escapeHtml(nombre)}</td>
                <td class="text-center">${escapeHtml(cedula)}</td>
                <td class="text-center acta-col-fn-d">${escapeHtml(diaFmt)}</td>
                <td class="text-center acta-col-fn-m">${escapeHtml(mesFmt)}</td>
                <td class="text-center acta-col-fn-a text-nowrap">${escapeHtml(anioFmt)}</td>
                <td class="text-center fw-bold">${escapeHtml(literal)}</td>
                <td class="text-center">${escapeHtml(obs)}</td>
            </tr>`;
        });

        tbodyDestino.innerHTML = contador > 0
            ? htmlRows
            : '<tr><td colspan="8" class="text-center py-3">Aún no ha completado filas de estudiantes para previsualizar.</td></tr>';
    }

    function configurarTogglePreview(btnToggleId, secFormId, secPrevId, tbodyOrigId, tbodyPrevId, inpAnioId, lblAnioId) {
        const btnToggle = document.getElementById(btnToggleId);
        const secForm = document.getElementById(secFormId);
        const secPrev = document.getElementById(secPrevId);
        const tbodyOrig = document.getElementById(tbodyOrigId);
        const tbodyPrev = document.getElementById(tbodyPrevId);
        const inpAnio = document.getElementById(inpAnioId);
        const lblAnio = document.getElementById(lblAnioId);

        if (!btnToggle || !secForm || !secPrev) return;

        btnToggle.addEventListener('click', () => {
            if (secPrev.classList.contains('d-none')) {
                actualizarFechasEncabezadoActa();
                construirVistaPrevia(tbodyOrig, tbodyPrev, inpAnio, lblAnio);
                secForm.classList.add('d-none');
                secPrev.classList.remove('d-none');
                btnToggle.innerHTML = '<i class="bi bi-pencil me-1"></i> Volver a la edición';
            } else {
                secPrev.classList.add('d-none');
                secForm.classList.remove('d-none');
                btnToggle.innerHTML = '<i class="bi bi-eye me-1"></i> Vista Previa del Acta';
            }
        });
    }

    configurarTogglePreview(
        'btnTogglePreviewCrear',
        'seccionFormularioCrearActa', 'seccionVistaPreviaCrearActa',
        'tbodyEstudiantesCrear', 'tbodyPreviewCrear',
        'crearAnioEscolar', 'previewCrearAnioTexto'
    );

    configurarTogglePreview(
        'btnTogglePreviewEditar',
        'seccionFormularioEditarActa', 'seccionVistaPreviaEditarActa',
        'tbodyEstudiantesEditar', 'tbodyPreviewEditar',
        'editarAnioEscolar', 'previewEditarAnioTexto'
    );

    // Validacion de ano escolar consecutivo con autocompletado
    function validarFormatoAnioEscolar(valor) {
        const raw = (valor || '').trim();
        if (!raw) {
            return { valido: false, mensaje: 'El campo Año Escolar es obligatorio.' };
        }
        if (/^\d{4}$/.test(raw)) {
            const y1 = parseInt(raw, 10);
            if (y1 >= 1900 && y1 <= 2100) {
                return { valido: true, valor: `${y1}-${y1 + 1}`, autocompletado: true };
            }
        }
        const normalizado = raw.replace(/\s*[\-–—\/]\s*/g, '-');
        const match = normalizado.match(/^(\d{4})-(\d{4})$/);
        if (!match) {
            return {
                valido: false,
                mensaje: 'El Año Escolar debe tener el formato AAAA-AAAA con años consecutivos (ejemplo: 2008-2009).'
            };
        }
        const y1 = parseInt(match[1], 10);
        const y2 = parseInt(match[2], 10);
        if (y2 !== y1 + 1) {
            return {
                valido: false,
                mensaje: `El segundo año (${y2}) debe ser estrictamente el siguiente al primero (${y1}). Ejemplo: ${y1}-${y1 + 1}.`
            };
        }
        return { valido: true, valor: `${y1}-${y2}` };
    }

    function vincularValidacionAnioEscolar(inputId, feedbackId) {
        const inp = document.getElementById(inputId);
        const feedback = document.getElementById(feedbackId);
        if (!inp) return;

        function validarYMostrar(autoCorregir = false) {
            const val = inp.value.trim();
            if (!val) {
                inp.classList.remove('is-invalid', 'is-valid');
                if (feedback) feedback.textContent = '';
                return false;
            }
            const res = validarFormatoAnioEscolar(val);
            if (!res.valido) {
                inp.classList.add('is-invalid');
                inp.classList.remove('is-valid');
                if (feedback) feedback.textContent = res.mensaje;
                return false;
            }
            if (autoCorregir || res.autocompletado || res.valor !== val) {
                inp.value = res.valor;
            }
            inp.classList.remove('is-invalid');
            inp.classList.add('is-valid');
            if (feedback) feedback.textContent = '';
            return true;
        }

        inp.addEventListener('blur', () => validarYMostrar(true));
        inp.addEventListener('input', () => {
            const raw = inp.value.trim();
            if (/^\d{4}$/.test(raw)) {
                validarYMostrar(true);
            } else if (inp.classList.contains('is-invalid') || inp.classList.contains('is-valid')) {
                validarYMostrar(false);
            }
        });
    }

    vincularValidacionAnioEscolar('crearAnioEscolar', 'feedbackCrearAnioEscolar');
    vincularValidacionAnioEscolar('editarAnioEscolar', 'feedbackEditarAnioEscolar');

    function vincularSubmitFormActa(formId, anioInputId, feedbackId, tbodyId) {
        const form = document.getElementById(formId);
        const inpAnio = document.getElementById(anioInputId);
        const feedback = document.getElementById(feedbackId);
        const tbody = document.getElementById(tbodyId);
        if (!form || !inpAnio) return;

        form.addEventListener('submit', (e) => {
            const res = validarFormatoAnioEscolar(inpAnio.value);
            if (!res.valido) {
                e.preventDefault();
                inpAnio.classList.add('is-invalid');
                if (feedback) feedback.textContent = res.mensaje;
                inpAnio.focus();
                alert(res.mensaje);
                return;
            }
            inpAnio.value = res.valor;

            if (tbody) {
                const filas = tbody.querySelectorAll('tr.fila-estudiante-acta');
                let tieneEstudiante = false;
                filas.forEach((fila) => {
                    const nombre = (fila.querySelector('.inp-nombre')?.value || '').trim();
                    if (nombre) tieneEstudiante = true;
                });
                if (!tieneEstudiante) {
                    e.preventDefault();
                    alert('Debe registrar al menos un estudiante con su nombre y fecha de nacimiento en el acta.');
                    const primerInp = tbody.querySelector('.inp-nombre');
                    if (primerInp) primerInp.focus();
                }
            }
        });
    }

    vincularSubmitFormActa('formCrearActa', 'crearAnioEscolar', 'feedbackCrearAnioEscolar', 'tbodyEstudiantesCrear');
    vincularSubmitFormActa('formEditarActa', 'editarAnioEscolar', 'feedbackEditarAnioEscolar', 'tbodyEstudiantesEditar');

    // Selector acumulativo de hasta 4 imagenes del libro fisico
    function configurarUploaderAcumulativo(inputId, previewBoxId, badgeId, getExistentesCountFn) {
        const inputEl = document.getElementById(inputId);
        const boxEl = document.getElementById(previewBoxId);
        const badgeEl = badgeId ? document.getElementById(badgeId) : null;
        if (!inputEl || !boxEl) return;

        const stagedFiles = [];

        function sincronizarDataTransfer() {
            const dt = new DataTransfer();
            stagedFiles.forEach((f) => dt.items.add(f));
            inputEl.files = dt.files;
        }

        function actualizarContador() {
            const existentes = getExistentesCountFn ? getExistentesCountFn() : 0;
            const total = existentes + stagedFiles.length;
            if (badgeEl) {
                badgeEl.textContent = `${total} de 4`;
                badgeEl.className = (total >= 4) ? 'badge bg-success rounded-pill px-2.5 py-1' : 'badge bg-primary rounded-pill px-2.5 py-1';
            }
        }

        function renderizarStagedPreviews() {
            boxEl.innerHTML = '';
            stagedFiles.forEach((file, idx) => {
                const col = document.createElement('div');
                col.className = 'col-6 col-md-3 item-img-staged';
                const fileUrl = URL.createObjectURL(file);
                const safeName = escapeHtml(file.name);
                col.innerHTML = `
                    <div class="card p-2 border shadow-sm bg-white text-center">
                        <img class="img-fluid rounded mb-1 acta-edit-thumb" src="${fileUrl}">
                        <div class="small text-truncate text-dark fw-semibold mb-1" title="${safeName}">Foto #${idx + 1}: ${safeName}</div>
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 py-0"><i class="bi bi-trash me-1"></i> Quitar</button>
                    </div>`;
                col.querySelector('button').addEventListener('click', () => {
                    stagedFiles.splice(idx, 1);
                    sincronizarDataTransfer();
                    renderizarStagedPreviews();
                });
                boxEl.appendChild(col);
            });
            actualizarContador();
        }

        inputEl.addEventListener('change', function () {
            const incoming = Array.from(this.files || []);
            if (incoming.length === 0) return;

            const existentes = getExistentesCountFn ? getExistentesCountFn() : 0;
            const cuposLibres = Math.max(0, 4 - (existentes + stagedFiles.length));

            if (cuposLibres <= 0) {
                alert('Ya ha completado el máximo permitido de 4 imágenes del libro físico.');
                sincronizarDataTransfer();
                return;
            }

            if (incoming.length > cuposLibres) {
                alert(`Solo puede añadir ${cuposLibres} imagen(es) más para no exceder el límite de 4 fotos.`);
            }

            const aceptados = incoming.slice(0, cuposLibres);
            aceptados.forEach((file) => {
                const duplicado = stagedFiles.some((f) => f.name === file.name && f.size === file.size);
                if (!duplicado) {
                    stagedFiles.push(file);
                }
            });

            sincronizarDataTransfer();
            renderizarStagedPreviews();
        });

        actualizarContador();
    }

    configurarUploaderAcumulativo('inputImagenesCrear', 'previewImagenesCrearBox', 'badgeContadorImagenesCrear', () => 0);
    configurarUploaderAcumulativo('inputImagenesEditar', 'previewImagenesEditarBox', 'badgeContadorImagenesEditar', () => {
        return document.querySelectorAll('#contenedorImagenesExistentesEditar .item-img-existente').length;
    });

    document.querySelectorAll('.btn-quitar-img-existente').forEach((btn) => {
        btn.addEventListener('click', function () {
            const item = this.closest('.item-img-existente');
            if (item) {
                item.remove();
                const badgeEl = document.getElementById('badgeContadorImagenesEditar');
                if (badgeEl) {
                    const restantes = document.querySelectorAll('#contenedorImagenesExistentesEditar .item-img-existente').length;
                    const input = document.getElementById('inputImagenesEditar');
                    const stagedCount = input && input.files ? input.files.length : 0;
                    const total = restantes + stagedCount;
                    badgeEl.textContent = `${total} de 4`;
                }
            }
        });
    });

    // Buscador asincrono de actas con filtros y paginacion por scroll
    const formBuscarActas = document.getElementById('formBuscarActas');
    if (formBuscarActas) {
        const inputBusqueda = document.getElementById('inputBusquedaActa');
        const btnLimpiarTexto = document.getElementById('btnLimpiarTextoActa');
        const filtroEstado = document.getElementById('filtroActaEstado');
        const filtroAnio = document.getElementById('filtroActaAnio');
        const filtroFnDia = document.getElementById('filtroFnDia');
        const filtroFnMes = document.getElementById('filtroFnMes');
        const filtroFnAnio = document.getElementById('filtroFnAnio');
        const btnAplicarModal = document.getElementById('btnAplicarFiltrosYBuscar');
        const btnLimpiarModal = document.getElementById('btnLimpiarModalFiltrosActas');
        const btnResetAll = document.getElementById('btnResetAllActaFilters');

        const badgeFiltrosCount = document.getElementById('badgeFiltrosActasCount');
        const activeFiltersBar = document.getElementById('activeActaFiltersBar');
        const activeFilterChips = document.getElementById('activeActaFilterChips');
        const contadorResultados = document.getElementById('contadorResultadosActas');

        const estadoInicial = document.getElementById('estadoInicialActas');
        const estadoCargando = document.getElementById('estadoCargandoActas');
        const estadoSinResultados = document.getElementById('estadoSinResultadosActas');
        const tituloSinResultados = document.getElementById('tituloSinResultadosActas');
        const mensajeSinResultados = document.getElementById('mensajeSinResultadosActas');
        const contenedorTabla = document.getElementById('contenedorTablaResultadosActas');
        const listaTarjetas = document.getElementById('listaTarjetasActas');
        const spinnerScroll = document.getElementById('spinnerScrollActas');

        let resultadosTotales = [];
        let itemsRenderizados = 0;
        const LIMITE_INICIAL_ACTAS = 10;
        const BATCH_SCROLL_ACTAS = 5;
        let cargandoBatchActas = false;
        let loadingTimerActas = null;
        let busquedaActivaController = null;

        function obtenerFechaFiltroFormateada() {
            const d = (filtroFnDia?.value || '').trim();
            const m = (filtroFnMes?.value || '').trim();
            const y = (filtroFnAnio?.value || '').trim();
            if (!d && !m && !y) return '';
            if (!d || !m || !y) return 'incompleta';
            return `${d.padStart(2, '0')}-${m.padStart(2, '0')}-${y}`;
        }

        function agregarChip(texto, onRemove) {
            const span = document.createElement('span');
            span.className = 'badge bg-white text-dark border d-inline-flex align-items-center py-1 px-2';
            span.textContent = `${texto} `;
            const btnClose = document.createElement('button');
            btnClose.type = 'button';
            btnClose.className = 'btn-close ms-2 filter-chip-close';
            btnClose.setAttribute('aria-label', 'Quitar filtro');
            btnClose.addEventListener('click', onRemove);
            span.appendChild(btnClose);
            activeFilterChips.appendChild(span);
        }

        function actualizarChipsFiltros() {
            let count = 0;
            activeFilterChips.innerHTML = '';

            const estVal = filtroEstado.value;
            if (estVal !== 'activo') {
                count++;
                const labelEst = estVal === 'inactivo' ? 'Estado: Desactivadas' : 'Estado: Todas';
                agregarChip(labelEst, () => { filtroEstado.value = 'activo'; ejecutarBusqueda(); });
            }

            const anioVal = filtroAnio.value.trim();
            if (anioVal !== '') {
                count++;
                agregarChip(`Año Escolar: ${anioVal}`, () => { filtroAnio.value = ''; ejecutarBusqueda(); });
            }

            const fnStr = obtenerFechaFiltroFormateada();
            if (fnStr !== '' && fnStr !== 'incompleta') {
                count++;
                agregarChip(`F. Nacimiento: ${fnStr}`, () => {
                    filtroFnDia.value = '';
                    filtroFnMes.value = '';
                    filtroFnAnio.value = '';
                    ejecutarBusqueda();
                });
            }

            if (count > 0) {
                badgeFiltrosCount.textContent = String(count);
                badgeFiltrosCount.classList.remove('d-none');
                activeFiltersBar.classList.remove('d-none');
            } else {
                badgeFiltrosCount.classList.add('d-none');
                activeFiltersBar.classList.add('d-none');
            }
        }

        function mostrarVistaEstado(nombreEstado) {
            estadoInicial.classList.add('d-none');
            estadoCargando.classList.add('d-none');
            estadoSinResultados.classList.add('d-none');
            contenedorTabla.classList.add('d-none');

            if (nombreEstado === 'inicial') estadoInicial.classList.remove('d-none');
            if (nombreEstado === 'cargando') estadoCargando.classList.remove('d-none');
            if (nombreEstado === 'vacio') estadoSinResultados.classList.remove('d-none');
            if (nombreEstado === 'tabla') contenedorTabla.classList.remove('d-none');
        }

        function formatearFechaEstudiante(fechaIso, anioFormato) {
            if (!fechaIso) return '';
            const partes = fechaIso.split('-');
            if (partes.length !== 3) return fechaIso;
            const y = (parseInt(anioFormato, 10) === 4) ? partes[0] : partes[0].slice(-2);
            return `${partes[2]}-${partes[1]}-${y}`;
        }

        function crearElementoTarjetaActa(acta) {
            const isActiva = parseInt(acta.activo, 10) === 1;
            const coincidencias = Array.isArray(acta.coincidencias) ? acta.coincidencias : [];
            const numActa = acta.numero_acta || 1;

            let midHtml = '';
            if (coincidencias.length > 0) {
                const headerMatch = acta.filtro_estudiante_activo
                    ? '<i class="bi bi-person-check-fill text-success me-1"></i>Coincidencia(s) en esta acta:'
                    : '<i class="bi bi-list-ul text-primary me-1"></i>Vista previa de graduados:';

                const itemsHtml = coincidencias.slice(0, 4).map((est) => {
                    const numOrden = String(est.orden || 1).padStart(2, '0');
                    const ciTxt = est.cedula ? ` • C.I: ${escapeHtml(est.cedula)}` : ' • Sin C.I.';
                    const fnTxt = est.fecha_nacimiento ? ` • F.Nac: ${escapeHtml(formatearFechaEstudiante(est.fecha_nacimiento, est.anio_formato))}` : '';
                    const litTxt = est.literal_nota ? ` • Literal: ${escapeHtml(est.literal_nota)}` : '';
                    const obsTxt = est.observacion ? ` (${escapeHtml(est.observacion)})` : '';
                    return `<div class="text-dark py-0.5 text-break">Nº ${numOrden}: ${escapeHtml(est.apellidos_nombres)}${ciTxt}${fnTxt}${litTxt}${obsTxt}</div>`;
                }).join('');

                const moreHtml = coincidencias.length > 4
                    ? `<div class="text-muted fst-italic mt-1">+ ${coincidencias.length - 4} coincidencia(s) adicional(es)...</div>`
                    : '';

                midHtml = `<div class="col-12 col-lg-6"><div class="bg-light rounded p-2 border small"><div class="fw-semibold text-secondary mb-1">${headerMatch}</div>${itemsHtml}${moreHtml}</div></div>`;
            } else {
                midHtml = '<div class="col-12 col-lg-6"></div>';
            }

            const col = document.createElement('div');
            col.className = 'col-12' + (!isActiva ? ' personal-card-inactivo' : '');
            col.innerHTML = `
                <div class="card card-custom h-100 p-3 border shadow-sm personal-row-hover cursor-pointer">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-lg-4">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary border d-flex align-items-center justify-content-center fw-bold flex-shrink-0 me-3 avatar-list-placeholder">
                                    <i class="bi bi-journal-bookmark-fill fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Año Escolar ${escapeHtml(acta.anio_escolar)} — Acta #${numActa}</h6>
                                    <div class="d-flex flex-wrap gap-1 align-items-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            <i class="bi bi-people-fill me-1"></i>${acta.total_estudiantes} graduado(s)
                                        </span>
                                        ${!isActiva ? '<span class="badge bg-secondary text-white">Inactiva</span>' : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                        ${midHtml}
                        <div class="col-12 col-lg-2 text-lg-end">
                            <a href="${baseUrl}/actas?id=${acta.id}" class="btn btn-sm btn-outline-primary fw-semibold px-3 w-100 w-lg-auto">
                                <i class="bi bi-folder2-open me-1"></i> Ver Acta
                            </a>
                        </div>
                    </div>
                </div>`;

            col.querySelector('.card').addEventListener('click', (e) => {
                if (e.target.closest('a, button')) return;
                window.location.href = `${baseUrl}/actas?id=${acta.id}`;
            });
            return col;
        }

        function cargarSiguienteLoteActas() {
            if (cargandoBatchActas) return;
            const total = resultadosTotales.length;
            if (itemsRenderizados >= total) return;

            if (itemsRenderizados === 0) {
                const limiteSiguiente = Math.min(LIMITE_INICIAL_ACTAS, total);
                const fragment = document.createDocumentFragment();
                for (let i = 0; i < limiteSiguiente; i++) {
                    fragment.appendChild(crearElementoTarjetaActa(resultadosTotales[i]));
                }
                listaTarjetas.appendChild(fragment);
                itemsRenderizados = limiteSiguiente;
                if (spinnerScroll) spinnerScroll.classList.add('d-none');
                return;
            }

            cargandoBatchActas = true;
            if (spinnerScroll) spinnerScroll.classList.remove('d-none');

            setTimeout(() => {
                const limiteSiguiente = Math.min(itemsRenderizados + BATCH_SCROLL_ACTAS, total);
                const fragment = document.createDocumentFragment();
                for (let i = itemsRenderizados; i < limiteSiguiente; i++) {
                    fragment.appendChild(crearElementoTarjetaActa(resultadosTotales[i]));
                }
                listaTarjetas.appendChild(fragment);
                itemsRenderizados = limiteSiguiente;

                if (spinnerScroll) spinnerScroll.classList.add('d-none');
                cargandoBatchActas = false;

                if (itemsRenderizados < total && (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 100)) {
                    setTimeout(cargarSiguienteLoteActas, 50);
                }
            }, 150);
        }

        function iniciarListaResultados() {
            listaTarjetas.innerHTML = '';
            itemsRenderizados = 0;
            if (spinnerScroll) spinnerScroll.classList.add('d-none');
            cargarSiguienteLoteActas();
        }

        let scrollTimeoutActas = null;
        window.addEventListener('scroll', () => {
            if (scrollTimeoutActas) return;
            scrollTimeoutActas = setTimeout(() => {
                scrollTimeoutActas = null;
                if (resultadosTotales.length === 0 || itemsRenderizados >= resultadosTotales.length) return;
                if ((window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 350)) {
                    cargarSiguienteLoteActas();
                }
            }, 80);
        }, { passive: true });

        function ejecutarBusqueda() {
            actualizarChipsFiltros();

            const q = inputBusqueda.value.trim();
            const anio = filtroAnio.value.trim();
            const estado = filtroEstado.value;
            const fnStr = obtenerFechaFiltroFormateada();

            if (loadingTimerActas) {
                clearTimeout(loadingTimerActas);
                loadingTimerActas = null;
            }

            if (busquedaActivaController) {
                busquedaActivaController.abort();
                busquedaActivaController = null;
            }

            if (fnStr === 'incompleta') {
                contadorResultados.textContent = '0';
                tituloSinResultados.textContent = 'Fecha de nacimiento incompleta';
                mensajeSinResultados.textContent = 'Por favor ingrese Día, Mes y Año completos en el filtro de fecha de nacimiento.';
                mostrarVistaEstado('vacio');
                return;
            }

            if (q === '' && anio === '' && fnStr === '' && estado !== 'inactivo') {
                contadorResultados.textContent = '0';
                tituloSinResultados.textContent = 'Ingrese un criterio de búsqueda';
                mensajeSinResultados.textContent = 'Escriba un nombre o cédula en el buscador, o seleccione un Año Escolar o Fecha de Nacimiento en Filtrar.';
                mostrarVistaEstado('vacio');
                return;
            }

            loadingTimerActas = setTimeout(() => {
                mostrarVistaEstado('cargando');
            }, 1000);

            busquedaActivaController = new AbortController();

            const params = new URLSearchParams({
                q: q,
                anio_escolar: anio,
                fecha_nacimiento: fnStr,
                estado: estado
            });

            fetch(`${baseUrl}/actas/search?${params.toString()}`, {
                signal: busquedaActivaController.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then((res) => res.json())
            .then((data) => {
                if (loadingTimerActas) {
                    clearTimeout(loadingTimerActas);
                    loadingTimerActas = null;
                }

                if (!data || !data.ok) {
                    contadorResultados.textContent = '0';
                    tituloSinResultados.textContent = 'Aviso de búsqueda';
                    mensajeSinResultados.textContent = (data && data.message) ? data.message : 'No fue posible realizar la búsqueda.';
                    mostrarVistaEstado('vacio');
                    return;
                }

                resultadosTotales = Array.isArray(data.actas) ? data.actas : [];
                resultadosTotales.sort((a, b) => {
                    const cA = parseInt(a.total_coincidencias || (a.coincidencias ? a.coincidencias.length : 0), 10);
                    const cB = parseInt(b.total_coincidencias || (b.coincidencias ? b.coincidencias.length : 0), 10);
                    if (cB !== cA) return cB - cA;
                    if (b.anio_escolar !== a.anio_escolar) return b.anio_escolar.localeCompare(a.anio_escolar);
                    return (parseInt(b.numero_acta || b.id, 10)) - (parseInt(a.numero_acta || a.id, 10));
                });
                contadorResultados.textContent = String(resultadosTotales.length);

                if (resultadosTotales.length === 0) {
                    tituloSinResultados.textContent = 'No se encontraron coincidencias';
                    mensajeSinResultados.textContent = 'No existen actas registradas con estudiantes que coincidan con los criterios indicados.';
                    mostrarVistaEstado('vacio');
                    return;
                }

                iniciarListaResultados();
                mostrarVistaEstado('tabla');
            })
            .catch((err) => {
                if (err && err.name === 'AbortError') return;
                if (loadingTimerActas) {
                    clearTimeout(loadingTimerActas);
                    loadingTimerActas = null;
                }
                contadorResultados.textContent = '0';
                tituloSinResultados.textContent = 'Error de conexión';
                mensajeSinResultados.textContent = 'Ocurrió un problema al consultar la base de datos. Intente nuevamente.';
                mostrarVistaEstado('vacio');
            });
        }

        formBuscarActas.addEventListener('submit', (e) => {
            e.preventDefault();
            ejecutarBusqueda();
        });

        if (btnAplicarModal) {
            btnAplicarModal.addEventListener('click', ejecutarBusqueda);
        }

        function limpiarFiltrosModal() {
            filtroEstado.value = 'activo';
            filtroAnio.value = '';
            filtroFnDia.value = '';
            filtroFnMes.value = '';
            filtroFnAnio.value = '';
            actualizarChipsFiltros();
        }

        if (btnLimpiarModal) {
            btnLimpiarModal.addEventListener('click', limpiarFiltrosModal);
        }

        if (btnResetAll) {
            btnResetAll.addEventListener('click', () => {
                if (loadingTimerActas) {
                    clearTimeout(loadingTimerActas);
                    loadingTimerActas = null;
                }
                if (busquedaActivaController) {
                    busquedaActivaController.abort();
                    busquedaActivaController = null;
                }
                limpiarFiltrosModal();
                inputBusqueda.value = '';
                contadorResultados.textContent = '0';
                mostrarVistaEstado('inicial');
            });
        }

        if (btnLimpiarTexto) {
            btnLimpiarTexto.addEventListener('click', () => {
                inputBusqueda.value = '';
                inputBusqueda.focus();
            });
        }
    }

    // Confirmacion de 5 segundos para desactivar acta
    configurarTemporizadorConfirmacion(
        document.getElementById('modalDesactivarActa'),
        document.getElementById('btnConfirmDesactivarActa'),
        '<i class="bi bi-slash-circle me-1"></i> Sí, Desactivar Acta'
    );

    // Zoom de imagenes del libro fisico
    document.querySelectorAll('.btn-zoom-imagen-acta').forEach((img) => {
        img.addEventListener('click', function () {
            mostrarModalZoom(
                'modalZoomImagenActa',
                'modalZoomImagenActaSrc',
                'modalZoomImagenActaTitulo',
                this.getAttribute('data-zoom-src') || '',
                this.getAttribute('data-zoom-title') || 'Respaldo del Libro Físico'
            );
        });
    });

    // Modal de descarga e impresion directa de actas
    const modalDescargaActaEl = document.getElementById('modalOpcionesDescargaActa');
    if (modalDescargaActaEl) {
        const modalDescargaActa = new bootstrap.Modal(modalDescargaActaEl);
        const labelTituloDescarga = document.getElementById('descargaActaTituloLabel');
        const btnDescargarImg = document.getElementById('btnOpcionDescargarImagenActa');
        const btnDescargarPdf = document.getElementById('btnOpcionDescargarPdfActa');
        const btnImprimirDirecto = document.getElementById('btnOpcionImprimirActa');

        const printTablaWrap = document.getElementById('printDirectTablaWrap');
        const printImagenWrap = document.getElementById('printDirectImagenWrap');
        const printImagenSrc = document.getElementById('printDirectImagenSrc');
        const printImagenSubtitulo = document.getElementById('printDirectImagenSubtitulo');

        const contextoDescarga = {
            tipo: 'acta',
            actaId: 0,
            imgId: 0,
            titulo: '',
            urlInline: '',
            urlDownload: '',
            urlPdf: ''
        };

        document.querySelectorAll('.btn-abrir-descarga-acta').forEach((btn) => {
            btn.addEventListener('click', function () {
                const tipo = this.getAttribute('data-tipo') || 'acta';
                const titulo = this.getAttribute('data-titulo') || 'Acta de Graduación';

                contextoDescarga.tipo = tipo;
                contextoDescarga.titulo = titulo;
                labelTituloDescarga.textContent = `"${titulo}"`;

                if (tipo === 'imagen') {
                    contextoDescarga.imgId = parseInt(this.getAttribute('data-img-id') || '0', 10);
                    contextoDescarga.urlInline = this.getAttribute('data-url-inline') || '';
                    contextoDescarga.urlDownload = this.getAttribute('data-url-download') || '';
                    contextoDescarga.urlPdf = this.getAttribute('data-url-pdf') || '#';
                } else {
                    contextoDescarga.actaId = parseInt(this.getAttribute('data-acta-id') || '0', 10);
                    contextoDescarga.urlPdf = `${baseUrl}/actas/printActa?acta_id=${contextoDescarga.actaId}`;
                }

                btnDescargarPdf.href = contextoDescarga.urlPdf;
                modalDescargaActa.show();
            });
        });

        btnDescargarPdf.addEventListener('click', () => {
            setTimeout(() => modalDescargaActa.hide(), 150);
        });

        btnDescargarImg.addEventListener('click', () => {
            if (contextoDescarga.tipo === 'imagen') {
                const a = document.createElement('a');
                a.href = contextoDescarga.urlDownload;
                a.download = '';
                document.body.appendChild(a);
                a.click();
                a.remove();
                modalDescargaActa.hide();
                return;
            }

            const captureEl = document.getElementById('actaDigitalCaptureArea');
            if (!captureEl) return;

            actualizarFechasEncabezadoActa();
            const originalText = btnDescargarImg.innerHTML;
            btnDescargarImg.disabled = true;
            btnDescargarImg.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Generando...';

            const opt = {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                scrollX: 0,
                scrollY: 0,
                x: 0,
                y: 0,
                width: 820,
                windowWidth: 820,
                logging: false
            };

            const promesa = (typeof window.html2canvas === 'function')
                ? window.html2canvas(captureEl, opt)
                : html2pdf().set({ html2canvas: opt }).from(captureEl).toCanvas().then(function () {
                    return this.get('canvas');
                });

            promesa.then((canvas) => {
                const link = document.createElement('a');
                const safeName = (contextoDescarga.titulo || 'Acta_Graduacion')
                    .replace(/[^a-zA-Z0-9_\-áéíóúÁÉÍÓÚñÑ ]/g, ' ')
                    .trim()
                    .replace(/\s+/g, '_') + '.png';
                link.download = safeName;
                link.href = canvas.toDataURL('image/png');
                document.body.appendChild(link);
                link.click();
                link.remove();
                btnDescargarImg.disabled = false;
                btnDescargarImg.innerHTML = originalText;
                modalDescargaActa.hide();
            }).catch((err) => {
                console.error('Error al generar imagen:', err);
                alert('No se pudo generar la imagen del acta.');
                btnDescargarImg.disabled = false;
                btnDescargarImg.innerHTML = originalText;
            });
        });

        btnImprimirDirecto.addEventListener('click', () => {
            const printEl = document.getElementById('documentoImpresionExpediente');
            if (printEl && printEl.parentElement !== document.body) {
                document.body.appendChild(printEl);
            }

            const fechaHora = formatearFechaHoraActual();
            document.querySelectorAll('.printDirectFechaHora, #printDirectFechaHora').forEach((el) => {
                el.textContent = fechaHora;
            });

            const lanzarPrint = () => {
                modalDescargaActa.hide();
                setTimeout(() => {
                    document.body.classList.add('imprimiendo-expediente');
                    window.print();
                    window.addEventListener('afterprint', () => {
                        document.body.classList.remove('imprimiendo-expediente');
                    }, { once: true });
                    setTimeout(() => {
                        document.body.classList.remove('imprimiendo-expediente');
                    }, 2500);
                }, 300);
            };

            if (contextoDescarga.tipo === 'imagen' && printImagenWrap && printTablaWrap && printImagenSrc) {
                printTablaWrap.classList.add('d-none');
                printImagenWrap.classList.remove('d-none');
                if (printImagenSubtitulo) {
                    printImagenSubtitulo.textContent = contextoDescarga.titulo;
                }
                if (printImagenSrc.getAttribute('src') !== contextoDescarga.urlInline) {
                    printImagenSrc.onload = () => {
                        printImagenSrc.onload = null;
                        lanzarPrint();
                    };
                    printImagenSrc.onerror = () => {
                        printImagenSrc.onerror = null;
                        lanzarPrint();
                    };
                    printImagenSrc.src = contextoDescarga.urlInline;
                } else {
                    lanzarPrint();
                }
            } else {
                if (printImagenWrap) printImagenWrap.classList.add('d-none');
                if (printTablaWrap) printTablaWrap.classList.remove('d-none');
                lanzarPrint();
            }
        });
    }

    // Exportacion a PDF en pestana independiente (expedientes y actas)
    const sheetContainerPdf = document.getElementById('sheetContainerPdf');
    if (sheetContainerPdf) {
        const fechaHora = formatearFechaHoraActual();
        const fechaHoraActual = document.getElementById('fechaHoraActual');
        if (fechaHoraActual) {
            fechaHoraActual.textContent = fechaHora;
        }
        document.querySelectorAll('.fechaHoraImpresion').forEach((elem) => {
            elem.textContent = fechaHora;
        });

        const btnCerrarPestana = document.getElementById('btnCerrarPestanaPdf');
        if (btnCerrarPestana) {
            btnCerrarPestana.addEventListener('click', () => window.close());
        }

        const pdfType = sheetContainerPdf.getAttribute('data-pdf-type') || 'expediente';
        const pdfFilename = sheetContainerPdf.getAttribute('data-pdf-filename') || 'Documento.pdf';

        function ajustarImagenHojaExpediente() {
            if (pdfType !== 'expediente') return;
            const img = sheetContainerPdf.querySelector('.doc-img-container img');
            const imgContainer = sheetContainerPdf.querySelector('.doc-img-container');
            if (!img || !imgContainer) return;

            if (window.innerWidth < 768) {
                imgContainer.style.height = '';
                imgContainer.style.maxHeight = '';
                img.style.maxHeight = '';
                return;
            }

            const header = sheetContainerPdf.querySelector('.header-print');
            const profile = sheetContainerPdf.querySelector('.profile-box');
            const title = sheetContainerPdf.querySelector('.doc-title');
            const desc = sheetContainerPdf.querySelector('.doc-desc-box');
            const footer = sheetContainerPdf.querySelector('.footer-vencimiento');

            const sheetInnerHeight = sheetContainerPdf.clientHeight - 40;
            const hHeader = header ? header.offsetHeight + 10 : 0;
            const hProfile = profile ? profile.offsetHeight + 10 : 0;
            const hTitle = title ? title.offsetHeight + 10 : 0;
            const hDesc = desc ? desc.offsetHeight + 10 : 0;
            const hFooter = footer ? footer.offsetHeight : 0;

            const espOcupado = hHeader + hProfile + hTitle + hDesc + hFooter;
            const maxImgH = Math.max(160, sheetInnerHeight - espOcupado - 16);

            imgContainer.style.height = `${maxImgH}px`;
            imgContainer.style.maxHeight = `${maxImgH}px`;
            img.style.maxHeight = `${maxImgH - 16}px`;
        }

        ajustarImagenHojaExpediente();

        function ejecutarDescargaPdf() {
            ajustarImagenHojaExpediente();
            const statusMsg = document.getElementById('pdfStatusMsg');
            if (statusMsg) {
                statusMsg.className = 'badge bg-warning text-dark ms-2';
                statusMsg.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Generando archivo PDF...';
            }

            const opt = {
                margin: [8, 10, 8, 10],
                filename: pdfFilename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, logging: false, scrollX: 0, scrollY: 0 },
                jsPDF: { unit: 'mm', format: 'letter', orientation: 'portrait' },
                pagebreak: { mode: pdfType === 'acta' ? ['css', 'legacy'] : ['avoid-all', 'css', 'legacy'] }
            };

            html2pdf().set(opt).from(sheetContainerPdf).save().then(() => {
                if (statusMsg) {
                    statusMsg.className = 'badge bg-success ms-2';
                    statusMsg.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> PDF guardado con éxito';
                }
            }).catch((err) => {
                if (statusMsg) {
                    statusMsg.className = 'badge bg-danger ms-2';
                    statusMsg.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Error al generar PDF';
                }
                console.error('Error al generar PDF:', err);
            });
        }

        const btnManual = document.getElementById('btnDescargarPdfManual');
        if (btnManual) {
            btnManual.addEventListener('click', ejecutarDescargaPdf);
        }
    }
});
