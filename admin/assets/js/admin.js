
class AnnouncementManager {
  constructor() {
    this.apiUrl = "//auth.xhus.cn/api/announcements?program_id=7";
  }

  fetchAnnouncements(callback) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", this.apiUrl, true);
    xhr.onload = function() {
      try {
        const data = JSON.parse(xhr.responseText);
        if (data.success && data.data) {
          callback(data.data);
        } else {
          callback([]);
        }
      } catch (error) {
        console.error("Failed to parse announcement data:", error);
        callback([]);
      }
    };
    xhr.onerror = function() {
      console.error("Failed to get announcement");
      callback([]);
    };
    xhr.withCredentials = false;
    xhr.send();
  }

  renderAnnouncement(announcement) {
    const title = this.escapeHtml(announcement.title);
    const content = this.escapeHtml(announcement.content)
      .replace(/\r\n/g, "<br>")
      .replace(/\n/g, "<br>")
      .replace(/\r/g, "<br>");
    return `
      <div class="announcement-item py-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h3 class="fs-sm fw-semibold mb-0">${title}</h3>
          <small class="text-muted">
            <i class="fa fa-calendar-alt opacity-50 me-1"></i>${this.formatDate(announcement.created_at)}
          </small>
        </div>
        <div class="fs-sm text-muted" style="white-space: pre-line;">${content}</div>
      </div>
    `;
  }

  escapeHtml(unsafe) {
    return unsafe
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  formatDate(dateString) {
    return new Date(dateString).toLocaleString("zh-CN", {
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
      hour12: false
    }).replace(/\//g, "-");
  }

  displayAnnouncements(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    this.fetchAnnouncements((announcements) => {
      if (announcements.length === 0) {
        container.innerHTML = `
          <div class="text-center py-3">
            <span class="text-muted fs-sm">
              <i class="fa fa-info-circle opacity-50 me-1"></i>暂无公告
            </span>
          </div>
        `;
        return;
      }
      
      const announcementsHtml = announcements.map(announcement => 
        this.renderAnnouncement(announcement)
      ).join('<hr class="my-0">');
      
      container.innerHTML = announcementsHtml;
    });
  }
}

class AdvertisementManager {
  constructor() {
    this.apiUrl = '//auth.xhus.cn/api/advertisements?program_id=7';
  }

  fetchAdvertisements(callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', this.apiUrl, true);
    xhr.onload = function() {
      try {
        const data = JSON.parse(xhr.responseText);
        if (data.success && data.data) {
          callback(data.data);
        } else {
          callback([]);
        }
      } catch (error) {
        console.error('Failed to parse ad data:', error);
        callback([]);
      }
    };
    xhr.onerror = function() {
      console.error('Failed to acquire ads');
      callback([]);
    };
    xhr.withCredentials = false;
    xhr.send();
  }

  renderAdvertisement(ad) {
    const title = this.escapeHtml(ad.title);
    const content = this.escapeHtml(ad.content)
      .replace(/\r\n/g, '<br>')
      .replace(/\n/g, '<br>')
      .replace(/\r/g, '<br>');
    const adLink = this.escapeHtml(ad.ad_link);
    const createdAt = this.formatDate(ad.created_at);
    
    return `
      <div class="ad-item py-2">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <a href="${adLink}" target="_blank" class="link-fx text-primary fw-semibold">${title}</a>
          <small class="text-muted">
            <i class="fa fa-calendar-alt opacity-50 me-1"></i>${createdAt}
          </small>
        </div>
        <div class="fs-sm" style="white-space: pre-line;">${content}</div>
      </div>
    `;
  }

  escapeHtml(unsafe) {
    return unsafe
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('zh-CN', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour12: false
    }).replace(/\//g, '-');
  }

  displayAdvertisements(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    this.fetchAdvertisements((advertisements) => {
      if (advertisements.length === 0) {
        container.innerHTML = `
          <div class="text-center py-3">
            <span class="text-muted fs-sm">
              <i class="fa fa-info-circle opacity-50 me-1"></i>暂无广告
            </span>
          </div>
        `;
        return;
      }
      
      const advertisementsHtml = advertisements.map(ad => 
        this.renderAdvertisement(ad)
      ).join('<hr class="my-0">');
      
      container.innerHTML = advertisementsHtml;
    });
  }
}

document.addEventListener('DOMContentLoaded', function () {
  const sidebar = document.querySelector('.sidebar');
  const headerToggle = document.querySelector('.header-toggle');
  const dropdownToggle = document.querySelector('.dropdown-toggle');
  const dropdownMenu = document.querySelector('.dropdown-menu');
  const mainContent = document.querySelector('.main-content');
  
  // 初始化公告管理器并显示公告
  const announcementManager = new AnnouncementManager();
  announcementManager.displayAnnouncements('announcements-container');
  
  // 初始化广告管理器并显示广告
  const adManager = new AdvertisementManager();
  adManager.displayAdvertisements('advertisements-container');
  if (headerToggle) {
    headerToggle.addEventListener('click', function (e) {
      e.stopPropagation();
      sidebar.classList.toggle('show');
      if (window.innerWidth <= 992) {
        if (sidebar.classList.contains('show')) {
          const overlay = document.createElement('div');
          overlay.className = 'sidebar-overlay';
          overlay.style.position = 'fixed';
          overlay.style.top = '0';
          overlay.style.left = '0';
          overlay.style.width = '100%';
          overlay.style.height = '100%';
          overlay.style.background = 'rgba(0,0,0,0.5)';
          overlay.style.zIndex = '1050';
          document.body.appendChild(overlay);
          overlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            document.body.removeChild(overlay);
          });
        } else {
          const overlay = document.querySelector('.sidebar-overlay');
          if (overlay) {
            document.body.removeChild(overlay);
          }
        }
      }
    });
  }
  document.addEventListener('click', function (e) {
    if (window.innerWidth <= 992 && sidebar && sidebar.classList.contains('show')) {
      if (!sidebar.contains(e.target) && e.target !== headerToggle) {
        sidebar.classList.remove('show');
        const overlay = document.querySelector('.sidebar-overlay');
        if (overlay) {
          document.body.removeChild(overlay);
        }
      }
    }
  });
  if (dropdownToggle && dropdownMenu) {
    dropdownToggle.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdownMenu.classList.toggle('show');
    });
    document.addEventListener('click', function (e) {
      if (dropdownMenu.classList.contains('show')) {
        if (!dropdownMenu.contains(e.target) && e.target !== dropdownToggle) {
          dropdownMenu.classList.remove('show');
        }
      }
    });
  }
  const currentPath = window.location.pathname;
  const sidebarLinks = document.querySelectorAll('.sidebar-menu a');

  sidebarLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (href && currentPath.includes(href)) {
      link.classList.add('active');
    }
  });
  const cards = document.querySelectorAll('.card');

  cards.forEach(card => {
    card.addEventListener('mouseenter', function () {
      this.style.transform = 'translateY(-5px)';
      this.style.boxShadow = '0 15px 30px rgba(31, 38, 135, 0.2)';
    });

    card.addEventListener('mouseleave', function () {
      this.style.transform = '';
      this.style.boxShadow = '';
    });
  });
  updateDateTime();
  setInterval(updateDateTime, 1000);

  function updateDateTime() {
    const datetimeElement = document.getElementById('current-datetime');
    if (datetimeElement) {
      const now = new Date();
      const formattedDate = `${now.getFullYear()}-${padZero(now.getMonth() + 1)}-${padZero(now.getDate())}`;
      const formattedTime = `${padZero(now.getHours())}:${padZero(now.getMinutes())}:${padZero(now.getSeconds())}`;
      datetimeElement.textContent = `${formattedDate} ${formattedTime}`;
    }
  }

  function padZero(num) {
    return num < 10 ? `0${num}` : num;
  }
});