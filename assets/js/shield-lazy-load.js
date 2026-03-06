document.addEventListener("DOMContentLoaded", function() {
    const shields = document.querySelectorAll('.wpes-placeholder');
    shields.forEach(function(shield) {
        shield.addEventListener('click', function() { wpesHandleClick(this); });
        shield.addEventListener('keydown', function(e) {
            if (e.which === 13 || e.which === 32) {
                e.preventDefault();
                wpesHandleClick(this);
            }
        });
    });

    function wpesHandleClick(element) {
        // --- Feature 4: Analytics with De-duplication ---
        if ( typeof wpes_vars !== 'undefined' ) {
            const videoId = element.getAttribute('data-video-id');
            const sessionKey = 'wpes_played_' + videoId;

            // Check if we already counted this video in this session
            if ( ! sessionStorage.getItem(sessionKey) ) {
                
                // Mark as played locally
                sessionStorage.setItem(sessionKey, '1');

                // Send the signal
                const data = new FormData();
                data.append('action', 'wpes_track_play');
                data.append('nonce', wpes_vars.nonce);
                
                if ( navigator.sendBeacon ) {
                    navigator.sendBeacon(wpes_vars.ajax_url, data);
                } else {
                    fetch(wpes_vars.ajax_url, { method: 'POST', body: data, keepalive: true });
                }
            }
        }
        // ------------------------------------------------

        const mode = element.getAttribute('data-mode');
        
        if ( mode === 'lightbox' ) {
            wpesOpenLightbox(element);
        } else {
            wpesLoadInline(element);
        }
    }

    function wpesLoadInline(element) {
        const iframe = wpesCreateIframe(element);
        element.innerHTML = '';
        element.appendChild(iframe);
    }

    function wpesOpenLightbox(element) {
        let modal = document.getElementById('wpes-lightbox-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'wpes-lightbox-modal';
            modal.className = 'wpes-lightbox-overlay';
            modal.innerHTML = `
                <div class="wpes-lightbox-content">
                    <button class="wpes-lightbox-close">&times;</button>
                    <div class="wpes-lightbox-video-wrap"></div>
                </div>
            `;
            document.body.appendChild(modal);
            
            modal.querySelector('.wpes-lightbox-close').addEventListener('click', wpesCloseLightbox);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) wpesCloseLightbox();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === "Escape" && modal.style.display === 'flex') wpesCloseLightbox();
            });
        }

        const container = modal.querySelector('.wpes-lightbox-video-wrap');
        container.innerHTML = ''; 
        const iframe = wpesCreateIframe(element);
        container.appendChild(iframe);

        modal.style.display = 'flex';
    }

    function wpesCloseLightbox() {
        const modal = document.getElementById('wpes-lightbox-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.querySelector('.wpes-lightbox-video-wrap').innerHTML = ''; 
        }
    }

    function wpesCreateIframe(element) {
        const videoId = element.getAttribute('data-video-id');
        const provider = element.getAttribute('data-provider');
        const start = element.getAttribute('data-start');
        const list = element.getAttribute('data-list');
        
        const iframe = document.createElement('iframe');
        
        if ( provider === 'vimeo' ) {
            let src = `https://player.vimeo.com/video/${videoId}?autoplay=1`;
            if (start) src += `#t=${start}`;
            iframe.src = src;
            iframe.title = "Vimeo video player";
        } else {
            let src = `https://www.youtube-nocookie.com/embed/${videoId}?autoplay=1`;
            if (start) src += `&start=${start.replace(/[^0-9]/g, '')}`; 
            if (list) src += `&list=${list}`;
            iframe.src = src;
            iframe.title = "YouTube video player";
        }

        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
        iframe.setAttribute('allowfullscreen', 'true');
        return iframe;
    }
});