/* Dashboard module: extracted from dashboard view for safer maintenance. */
// Global Local Storage / Memory State synchronized via PHP
const DASHBOARD_CONFIG = window.DASHBOARD_CONFIG || {};
const PHP_CURRENT_USER = DASHBOARD_CONFIG.currentUser || {};

// In memory databases updated dynamically
let cacheStaff = [];
let cacheTasks = [];
let cacheLogs = [];
let cacheStats = null;
let cachePositions = [];
let cacheJobCategories = [];
let cachePermissions = [];

// Initialize Lucide Icons initially
lucide.createIcons();

// On document ready
window.addEventListener("DOMContentLoaded", () => {
  // Pre-load Date Inputs to today
  const todayStr =
    DASHBOARD_CONFIG.today || new Date().toISOString().slice(0, 10);
  const logDateInput = document.getElementById("log-date");
  if (logDateInput) logDateInput.value = todayStr;

  // Load all database parameters from endpoint
  syncData();
});

// ----------------------------------------------------
// REFRESH & SYNCHRONIZE DATA VIA BACKEND ENDPOINTS
// ----------------------------------------------------
async function syncData() {
  try {
    // Parrallel requests to API Controllers
    const [staffRes, tasksRes, logsRes, statsRes, categoriesRes] =
      await Promise.all([
        fetch(DASHBOARD_CONFIG.api.users),
        fetch(DASHBOARD_CONFIG.api.tasks),
        fetch(DASHBOARD_CONFIG.api.logs),
        fetch(DASHBOARD_CONFIG.api.stats),
        fetch(DASHBOARD_CONFIG.api.categories),
      ]);

    if (staffRes.ok) cacheStaff = await staffRes.json();
    if (tasksRes.ok) cacheTasks = await tasksRes.json();
    if (logsRes.ok) cacheLogs = await logsRes.json();
    if (statsRes.ok) cacheStats = await statsRes.json();
    if (categoriesRes && categoriesRes.ok) {
      const cats = await categoriesRes.json();
      cachePositions = cats.positions || [];
      cacheJobCategories = cats.jobCategories || [];
      cachePermissions = cats.permissions || [];
    }

    // Build frontend layouts based on results
    renderDashboardMetrics();
    renderStaffTable();
    renderTasksTimeline();
    renderProgressLogs();
    renderPerformanceReports();
    renderMasterCategories();

    // Form elements update
    populateWorkersSelections();
    populateWorkerTaskSelect();
    populateLogTaskFilterOptions();
  } catch (err) {
    console.error("Database connection snapped: ", err);
    showToast(
      "alarm",
      "Kết nối chậm",
      "Không thể lấy số liệu gỗ thực tế hoặc CSDL MySQL tạm nghỉ."
    );
  }
}

// ----------------------------------------------------
// TAB SWAP CONTROLLER
// ----------------------------------------------------
function switchTab(tabId) {
  // Hide all tabs
  document.querySelectorAll(".viewport-tab").forEach((section) => {
    section.classList.add("hidden");
  });
  // Show requested tab
  const targetEl = document.getElementById("viewport-" + tabId);
  if (targetEl) {
    targetEl.classList.remove("hidden");
  }

  // Un-active all desk buttons
  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.className =
      "tab-btn w-full text-xs font-bold px-4 py-3 rounded-xl flex items-center gap-3 transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50";
  });

  // Set current active button
  const activeBtn = document.getElementById("tab-btn-" + tabId);
  if (activeBtn) {
    activeBtn.className =
      "tab-btn w-full text-xs font-bold px-4 py-3 rounded-xl flex items-center gap-3 transition-all cursor-pointer bg-slate-900 text-white shadow-sm font-extrabold";
  }

  // Close mobile menu if responsive
  const drawer = document.getElementById("mobile-menu-drawer");
  drawer.classList.add("hidden");

  // Quick sync when swapping
  syncData();
}

function toggleMobileMenu() {
  const drawer = document.getElementById("mobile-menu-drawer");
  if (drawer.classList.contains("hidden")) {
    drawer.classList.remove("hidden");
  } else {
    drawer.classList.add("hidden");
  }
}

function getAppBaseUrl() {
  const apiLogsUrl = String(DASHBOARD_CONFIG?.api?.logs || "").trim();
  if (!apiLogsUrl) {
    return window.location.origin;
  }

  return apiLogsUrl.replace(/\/api\/logs(?:\/.*)?$/i, "");
}

function normalizeAttachmentUrl(rawUrl) {
  const url = String(rawUrl || "").trim();
  if (!url) return "";

  if (/^data:/i.test(url) || /^https?:\/\//i.test(url)) {
    try {
      const parsed = new URL(url);
      const appBase = getAppBaseUrl();
      const appBaseParsed = new URL(appBase, window.location.origin);

      // Fix URLs accidentally built at host root like /uploads/... when app is in subfolder.
      if (
        parsed.origin === appBaseParsed.origin &&
        /^\/uploads\//i.test(parsed.pathname) &&
        /\/public$/i.test(appBaseParsed.pathname)
      ) {
        return `${appBaseParsed.origin}${appBaseParsed.pathname}${parsed.pathname}${parsed.search}${parsed.hash}`;
      }
    } catch (_) {
      // Keep original URL when parsing fails.
    }
    return url;
  }

  const appBase = getAppBaseUrl();
  if (/^\/uploads\//i.test(url)) {
    return `${appBase}${url}`;
  }

  if (/^uploads\//i.test(url)) {
    return `${appBase}/${url}`;
  }

  if (/^\/public\/uploads\//i.test(url)) {
    return `${appBase}${url.replace(/^\/public/i, "")}`;
  }

  return `${appBase}/${url.replace(/^\/+/, "")}`;
}

function resolveAttachmentUrl(log) {
  const candidates = [
    log?.image,
    log?.attachment,
    log?.attachment_url,
    log?.file_url,
    log?.file_path,
    log?.image_url,
  ];

  for (const raw of candidates) {
    const normalized = normalizeAttachmentUrl(raw);
    if (normalized) return normalized;
  }

  return "";
}

function getAttachmentMeta(attachmentUrl, mimeHint = "") {
  const url = String(attachmentUrl || "").trim();
  const mime = String(mimeHint || "")
    .toLowerCase()
    .trim();
  if (!url) {
    return { url: "", ext: "", isImage: false, isDocument: false };
  }

  const cleanUrl = url.split("?")[0].split("#")[0];
  const ext = cleanUrl.includes(".")
    ? cleanUrl.split(".").pop().toLowerCase()
    : "";

  const imageExt = ["png", "jpg", "jpeg", "gif", "webp", "bmp", "svg"];
  const documentExt = ["pdf", "doc", "docx", "xls", "xlsx"];

  const isDataImage = url.startsWith("data:image/");
  const isMimeImage = mime.startsWith("image/");
  const isMimeDocument = mime.startsWith("application/");
  const looksLikeImageUrl =
    /images\.unsplash\.com|imgur\.com|cloudinary|\/uploads\/progress_logs\//i.test(
      url
    ) || /[?&](fm|format)=(jpg|jpeg|png|webp|gif)/i.test(url);

  const isDocumentByExt = documentExt.includes(ext);
  const isImageByExt = imageExt.includes(ext);
  const isDocument = isDocumentByExt || (isMimeDocument && !isMimeImage);
  const isImage =
    isImageByExt ||
    isDataImage ||
    isMimeImage ||
    (!isDocument && looksLikeImageUrl);

  return {
    url,
    ext,
    isImage,
    isDocument,
  };
}

function renderTimelineAttachment(log) {
  const attachment = getAttachmentMeta(
    resolveAttachmentUrl(log),
    log.image_mime || log.mime_type || log.attachment_mime
  );
  if (!attachment.url) return "";

  if (attachment.isImage) {
    return `
      <a href="${attachment.url}" target="_blank" rel="noopener noreferrer" class="relative block max-w-[124px] rounded-lg overflow-hidden border border-slate-150 mt-1 cursor-zoom-in" title="Mở ảnh minh chứng">
        <img src="${attachment.url}" class="w-full h-14 object-cover hover:scale-105 transition-all">
      </a>`;
  }

  const icon = attachment.ext === "pdf" ? "file-text" : "file-spreadsheet";
  const extLabel = attachment.ext ? attachment.ext.toUpperCase() : "FILE";
  return `
    <a href="${attachment.url}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-[10px] font-bold">
      <i data-lucide="${icon}" class="w-3.5 h-3.5 text-indigo-600"></i>
      Xem tài liệu (${extLabel})
    </a>`;
}

function renderReviewAttachment(log, progressValue) {
  const attachment = getAttachmentMeta(
    resolveAttachmentUrl(log),
    log.image_mime || log.mime_type || log.attachment_mime
  );
  const progressBadge = `<div class="absolute bottom-2 left-2 bg-slate-900/85 text-white px-2.5 py-1 rounded-md text-[10px] font-extrabold tracking-wide shadow">${progressValue}% hoàn thiện</div>`;

  if (attachment.url && attachment.isImage) {
    return `
      <div class="md:w-56 overflow-hidden bg-slate-100 relative max-h-[160px] md:max-h-none flex items-center justify-center shrink-0 border-b md:border-b-0 md:border-r border-slate-150">
        <a href="${attachment.url}" target="_blank" rel="noopener noreferrer" class="block w-full h-full">
          <img src="${attachment.url}" alt="Báo cáo" class="w-full h-full object-cover">
        </a>
        ${progressBadge}
      </div>`;
  }

  if (attachment.url && attachment.isDocument) {
    const icon = attachment.ext === "pdf" ? "file-text" : "file-spreadsheet";
    const extLabel = attachment.ext.toUpperCase();
    return `
      <div class="md:w-56 bg-slate-50 relative max-h-[160px] md:max-h-none flex flex-col items-center justify-center shrink-0 border-b md:border-b-0 md:border-r border-slate-150 p-4 text-center gap-2">
        <i data-lucide="${icon}" class="w-8 h-8 text-indigo-600"></i>
        <span class="text-[11px] font-bold text-slate-700">Tài liệu ${extLabel}</span>
        <a href="${attachment.url}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2 py-1 rounded-md border border-slate-200 bg-white hover:bg-slate-50 text-[10px] font-bold text-slate-700">Mở tệp</a>
        ${progressBadge}
      </div>`;
  }

  return `
    <div class="md:w-56 overflow-hidden bg-slate-100 relative max-h-[160px] md:max-h-none flex items-center justify-center shrink-0 border-b md:border-b-0 md:border-r border-slate-150">
      <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80" alt="Báo cáo" class="w-full h-full object-cover">
      ${progressBadge}
    </div>`;
}

// ----------------------------------------------------
// DYNAMIC VIEWPORT 1: DASHBOARD METRICS
// ----------------------------------------------------
function renderDashboardMetrics() {
  if (!cacheStats) return;
  const s = cacheStats.summary;
  document.getElementById("kpi-staff").innerText = s.totalStaff + " người";
  document.getElementById("kpi-tasks").innerText = s.totalTasks + " việc";
  document.getElementById("kpi-active-tasks").innerText =
    s.inProgressTasks + " việc";
  document.getElementById("kpi-done-tasks").innerText =
    s.completedTasks + " việc";

  // Top employer avatar block
  const bestPerformerWidget = document.getElementById("best-performer-widget");
  const productivity = cacheStats.employeeProductivity || [];

  if (productivity.length > 0) {
    const top = [...productivity].sort(
      (a, b) => b.totalProgressPoints - a.totalProgressPoints
    )[0];
    if (top) {
      bestPerformerWidget.innerHTML = `
                        <img src="${top.avatar}" alt="Top worker" class="w-11 h-11 rounded-full object-cover border-2 border-indigo-500/50">
                        <div>
                            <h4 class="font-bold text-sm text-slate-100">${top.name}</h4>
                            <span class="block text-[10px] text-amber-300 font-bold uppercase tracking-wider">Tích lũy ${top.totalProgressPoints}% tiến độ</span>
                        </div>
                    `;
    }
  } else {
    bestPerformerWidget.innerHTML = `<span class="text-xs text-slate-400">Chưa ghi nhận hoạt động</span>`;
  }

  // Recent logs inline feed
  const container = document.getElementById("dashboard-recent-logs-list");
  if (cacheLogs.length === 0) {
    container.innerHTML = `<p class="text-xs text-slate-400 italic text-center py-6">Chưa có báo cáo nào được gửi hôm nay.</p>`;
    return;
  }

  let logsHTML = "";
  cacheLogs.slice(0, 5).forEach((log) => {
    let badgeStyle = "bg-amber-50 text-amber-700";
    let badgeText = "Chờ duyệt";
    if (log.status === "approved") {
      badgeStyle =
        log.auto_approved == 1
          ? "bg-slate-50 text-slate-500"
          : "bg-emerald-50 text-emerald-700";
      badgeText = log.auto_approved == 1 ? "Tự động duyệt" : "Đã thông qua";
    } else if (log.status === "rejected") {
      badgeStyle = "bg-rose-50 text-rose-700";
      badgeText = "Từ chối";
    }

    logsHTML += `
                    <div class="py-3 flex items-start gap-3 text-xs">
                        <img src="${log.user_avatar}" alt="Avatar" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-100">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-slate-800">${log.user_name}</span>
                                <span class="text-[9px] text-slate-400 font-mono">${log.date}</span>
                            </div>
                            <p class="text-slate-500 text-[11px] truncate mt-0.5">Quy cách: <b>${log.task_title}</b> (${log.progress_percent}%) - ${log.notes}</p>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold shrink-0 ${badgeStyle}">${badgeText}</span>
                    </div>
                `;
  });
  container.innerHTML = logsHTML;
}

// ----------------------------------------------------
// DYNAMIC VIEWPORT 2: STAFF CRUD
// ----------------------------------------------------
function renderStaffTable() {
  const tbody = document.getElementById("staff-table-body");
  tbody.innerHTML = "";

  if (cacheStaff.length === 0) {
    tbody.innerHTML = `<tr><td colSpan="6" class="text-center py-10 text-slate-400 italic">Không tìm thấy nhân sự nào.</td></tr>`;
    return;
  }

  cacheStaff.forEach((user) => {
    // Role translation
    let roleBadge = "bg-blue-50 text-blue-700";
    let roleName = "Nhân viên";
    if (user.role === "admin") {
      roleBadge = "bg-rose-50 text-rose-700";
      roleName = "Quản trị tối cao";
    } else if (user.role === "manager") {
      roleBadge = "bg-amber-50 text-amber-700";
      roleName = "Quản lý";
    }

    // Render options buttons based on admin/permission
    let optionsBtn = "-";
    if (PHP_CURRENT_USER.role === "admin") {
      optionsBtn = `
                        <button onclick="editStaff('${user.id}')" class="p-1 text-slate-400 hover:text-indigo-600 cursor-pointer" title="Cập nhật hồ sơ"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
                        <button onclick="deleteStaff('${user.id}')" class="p-1 text-slate-400 hover:text-rose-600 cursor-pointer" title="Xóa nhân viên"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                    `;
    }

    const positionNameHtml = user.position_name
      ? `<span class="font-bold text-slate-800 block">${user.position_name}</span>`
      : `<span class="text-slate-400 italic">Chưa giao vị</span>`;
    const permissionsList = user.custom_permissions || [];
    const permissionsCell =
      permissionsList.length > 0
        ? `<div class="flex flex-wrap gap-1 justify-center">${permissionsList.map((p) => `<span class="bg-slate-100 text-slate-600 text-[8px] font-mono px-1 py-0.2 rounded font-semibold">${p}</span>`).join("")}</div>`
        : `<span class="text-slate-300 italic text-[9px]">Không có</span>`;

    const tr = document.createElement("tr");
    tr.className = "hover:bg-slate-50/50 transition-colors";
    tr.innerHTML = `
                    <td class="py-3 px-4 flex items-center gap-2.5">
                        <img src="${user.avatar}" alt="Avatar" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-100">
                        <div>
                            <span class="block font-bold text-slate-800 text-xs">${user.name}</span>
                            <span class="text-[9px] text-slate-400 block font-mono">ID: ${user.id}</span>
                        </div>
                    </td>
                    <td class="py-3 px-4 font-mono font-bold text-slate-700 text-xs">${user.phone}</td>
                    <td class="py-3 px-4">${positionNameHtml}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded-full ${roleBadge}">${roleName}</span>
                    </td>
                    <td class="py-3 px-4 text-center">${permissionsCell}</td>
                    <td class="py-3 px-4 text-right">
                        <div class="flex justify-end gap-1.5">${optionsBtn}</div>
                    </td>
                `;
    tbody.appendChild(tr);
  });
  lucide.createIcons();
}

function filterStaffTable() {
  const kw = document.getElementById("staff-search").value.toLowerCase().trim();
  const rows = document.querySelectorAll("#staff-table-body tr");
  rows.forEach((row) => {
    const text = row.innerText.toLowerCase();
    if (text.includes(kw)) {
      row.classList.remove("hidden");
    } else {
      row.classList.add("hidden");
    }
  });
}

// Staff CRUD Submissions
let staffAvatarBase64 = null;

function openStaffModal() {
  document.getElementById("staff-modal").classList.remove("hidden");
  document.getElementById("staff-modal-title").innerText =
    "Thêm Hồ Sơ Nhân Viên";
  document.getElementById("staff-edit-id").value = "";
  document.getElementById("staff-form").reset();
  document.getElementById("pwd-required-star").style.display = "inline";
  document.getElementById("staff-avatar-preview").src =
    "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150";
  staffAvatarBase64 = null;
  renderStaffPermissionOptions();
  adjustPermissionCheckboxesByRole();
}

function closeStaffModal() {
  document.getElementById("staff-modal").classList.add("hidden");
}

function renderStaffPermissionOptions(selectedPermissions = []) {
  const container = document.getElementById("staff-permissions-grid");
  if (!container) return;

  container.innerHTML = "";

  if (!Array.isArray(cachePermissions) || cachePermissions.length === 0) {
    container.innerHTML = `<span class="text-slate-400 italic text-[11px]">Chưa có quyền nào trong danh mục.</span>`;
    return;
  }

  const selectedSet = new Set(
    Array.isArray(selectedPermissions) ? selectedPermissions : []
  );

  cachePermissions.forEach((perm) => {
    const label = document.createElement("label");
    label.className = "flex items-center gap-2 cursor-pointer select-none";
    label.innerHTML = `
                    <input type="checkbox" name="permissions" value="${perm.id}" class="rounded text-slate-900 focus:ring-0" ${selectedSet.has(perm.id) ? "checked" : ""}>
                    <span>${perm.id}: ${perm.name}</span>
                `;
    container.appendChild(label);
  });
}

function adjustPermissionCheckboxesByRole() {
  const role = document.getElementById("staff-role").value;
  const checkboxes = document.querySelectorAll('input[name="permissions"]');

  checkboxes.forEach((cb) => {
    cb.checked = false;
    if (role === "admin") {
      cb.checked = true;
    } else if (role === "manager") {
      if (["p1", "p2", "p3", "p6"].includes(cb.value)) {
        cb.checked = true;
      }
    } else {
      if (["p1"].includes(cb.value)) {
        cb.checked = true;
      }
    }
  });
}

function previewStaffAvatar(event) {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function (e) {
    const img = new Image();
    img.onload = function () {
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d");

      const maxDim = 160;
      let width = img.width;
      let height = img.height;

      if (width > height) {
        if (width > maxDim) {
          height = Math.round((height * maxDim) / width);
          width = maxDim;
        }
      } else {
        if (height > maxDim) {
          width = Math.round((width * maxDim) / height);
          height = maxDim;
        }
      }

      canvas.width = width;
      canvas.height = height;
      ctx.drawImage(img, 0, 0, width, height);

      const compressedBase64 = canvas.toDataURL("image/jpeg", 0.85);
      document.getElementById("staff-avatar-preview").src = compressedBase64;
      staffAvatarBase64 = compressedBase64;
    };
    img.src = e.target.result;
  };
  reader.readAsDataURL(file);
}

async function handleStaffSubmit(event) {
  event.preventDefault();
  const editId = document.getElementById("staff-edit-id").value;
  const name = document.getElementById("staff-name").value.trim();
  const phone = document.getElementById("staff-phone").value.trim();
  const identity_card = document.getElementById("staff-ic").value.trim();
  const dob = document.getElementById("staff-dob").value;
  const address = document.getElementById("staff-address").value.trim();
  const role = document.getElementById("staff-role").value;
  const position_id = document.getElementById("staff-position-id").value;
  const password = document.getElementById("staff-password").value;

  // Permissions array
  const custom_permissions = [];
  document
    .querySelectorAll('input[name="permissions"]:checked')
    .forEach((cb) => {
      custom_permissions.push(cb.value);
    });

  const bodyData = {
    name,
    phone,
    identity_card,
    dob,
    address,
    role,
    position_id,
    custom_permissions,
  };
  if (password) {
    bodyData.password = password;
  }
  if (staffAvatarBase64) {
    bodyData.avatar = staffAvatarBase64;
  }

  try {
    let url = DASHBOARD_CONFIG.api.users;
    let method = "POST";

    if (editId) {
      url = DASHBOARD_CONFIG.api.users + "/" + editId;
      method = "PUT";
    }

    const response = await fetch(url, {
      method: method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(bodyData),
    });

    const resData = await response.json();

    if (response.ok) {
      showToast(
        "success",
        "Lưu thông số",
        editId
          ? "Cập nhật hồ sơ nhân viên thành công!"
          : "Thêm mới hồ sơ nhân viên thành công."
      );
      closeStaffModal();
      syncData();
    } else {
      showToast(
        "alarm",
        "Ghi hồ sơ lỗi",
        resData.messages?.error ||
          resData.message ||
          "Vui lòng kiểm tra lại thông số"
      );
    }
  } catch (err) {
    console.error(err);
    showToast("alarm", "Lỗi PHP", "Lỗi máy chủ cơ sở dữ liệu PHP cũ.");
  }
}

function editStaff(userId) {
  const user = cacheStaff.find((u) => u.id === userId);
  if (!user) return;

  openStaffModal();
  document.getElementById("staff-modal-title").innerText =
    "Hiệu Chỉnh Hồ Sơ Nhân Viên: " + user.name;
  document.getElementById("staff-edit-id").value = user.id;

  document.getElementById("staff-name").value = user.name;
  document.getElementById("staff-phone").value = user.phone;
  document.getElementById("staff-ic").value = user.identity_card;
  document.getElementById("staff-dob").value = user.dob || "";
  document.getElementById("staff-address").value = user.address || "";
  document.getElementById("staff-role").value = user.role;
  document.getElementById("staff-position-id").value = user.position_id || "";
  document.getElementById("pwd-required-star").style.display = "none";
  document.getElementById("staff-avatar-preview").src =
    user.avatar ||
    "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150";
  staffAvatarBase64 = null;

  // Check permissions
  const permissionsList = user.custom_permissions || [];
  renderStaffPermissionOptions(permissionsList);
}

async function deleteStaff(userId) {
  if (!confirm("Bạn có chắc muốn xóa hồ sơ nhân viên này khỏi hệ thống?"))
    return;

  try {
    const response = await fetch(DASHBOARD_CONFIG.api.users + "/" + userId, {
      method: "DELETE",
    });

    if (response.ok) {
      showToast(
        "success",
        "Đã xóa nhân sự",
        "Hồ sơ nhân viên đã được gỡ khỏi hệ thống."
      );
      syncData();
    } else {
      showToast(
        "alarm",
        "Lỗi thi hành",
        "Không có quyền gỡ bỏ hoặc ràng buộc dữ liệu."
      );
    }
  } catch (err) {
    console.error(err);
  }
}

// ----------------------------------------------------
// DYNAMIC VIEWPORT 4: TASKS MANAGEMENT & PLANNING
// ----------------------------------------------------
function normalizeCategoryName(value) {
  return String(value || "")
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .trim();
}

function getTaskCategoryBadgeClass(categoryName) {
  const normalized = normalizeCategoryName(categoryName);

  if (normalized.includes("ky thuat")) {
    return "bg-cyan-50 text-cyan-700 border-cyan-200";
  }

  if (normalized.includes("hanh chinh")) {
    return "bg-orange-50 text-orange-700 border-orange-200";
  }

  return "bg-indigo-50 text-indigo-700 border-indigo-100";
}

function getTaskDerivedStatus(taskId) {
  const taskLogs = cacheLogs.filter(
    (l) => l.task_id === taskId || l.taskId === taskId
  );

  if (taskLogs.length === 0) {
    return { status: "pending", progress: 0 };
  }

  const maxProgress = Math.max(
    ...taskLogs.map((l) => Number(l.progress_percent || l.progressPercent || 0))
  );

  if (maxProgress >= 100) {
    return { status: "completed", progress: 100 };
  }

  if (maxProgress > 0) {
    return { status: "in_progress", progress: maxProgress };
  }

  return { status: "pending", progress: 0 };
}

function renderTasksTimeline() {
  const container = document.getElementById("tasks-timeline-container");
  container.innerHTML = "";

  if (cacheTasks.length === 0) {
    container.innerHTML = `
                    <div class="text-center py-12 bg-white border border-slate-200 rounded-2xl italic text-slate-400 text-xs">
                        Chưa xây dựng mục phân giao công việc chế tác nào.
                    </div>
                `;
    return;
  }

  cacheTasks.forEach((task) => {
    const derivedState = getTaskDerivedStatus(task.id);
    const progressVal = derivedState.progress;
    const taskStatus = derivedState.status;
    const categoryBadgeClass = getTaskCategoryBadgeClass(
      task.job_category_name
    );

    // Status badging
    let badgeStyle = "bg-amber-50 text-amber-700";
    let badgeText = "Chờ bắt đầu";
    if (taskStatus === "completed") {
      badgeStyle = "bg-emerald-50 text-emerald-700";
      badgeText = "Hoàn thành";
    } else if (taskStatus === "in_progress") {
      badgeStyle = "bg-indigo-50 text-indigo-700";
      badgeText = "Đang thi công";
    }

    // Render workers images
    let assignedGridHTML = "";
    const assignees = task.assigned_users || [];
    if (assignees.length > 0) {
      assignedGridHTML = `
                        <div class="flex items-center gap-1">
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mr-1">Tổ phụ trách:</span>
                            <div class="flex -space-x-2 overflow-hidden">
                                ${assignees
                                  .map(
                                    (u) => `
                                    <img src="${u.avatar}" alt="${u.name}" class="inline-block h-6 w-6 rounded-full object-cover ring-2 ring-white" title="${u.name} (${u.position_name || "Nhân viên"})">
                                `
                                  )
                                  .join("")}
                            </div>
                        </div>
                    `;
    } else {
      assignedGridHTML = `<span class="text-[10px] text-amber-600 font-medium italic">Chưa giao cho nhân viên nào</span>`;
    }

    // Create Task visual block
    const div = document.createElement("div");
    div.className =
      "bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm space-y-3 hover:border-slate-300 transition-all";

    // Allow dynamic admin/manager controls
    let editTaskControls = "";
    if (PHP_CURRENT_USER.role !== "staff") {
      editTaskControls = `
                        <button onclick="editTask('${task.id}')" class="p-1 text-slate-400 hover:text-indigo-600 cursor-pointer" title="Sửa công việc"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
                        <button onclick="deleteTask('${task.id}')" class="p-1 text-slate-400 hover:text-rose-600 cursor-pointer" title="Hủy bỏ việc này"><i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-500"></i></button>
                    `;
    }

    div.innerHTML = `
                    <div class="flex justify-between items-start gap-3">
                        <div>
                <span class="${categoryBadgeClass} border text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md">${task.job_category_name || "Công việc chung"}</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1.5">${task.title}</h3>
                            <p class="text-[10px] text-slate-400 font-mono mt-0.5">Ngày chạy: ${task.start_date} ~ Hạn định: ${task.end_date}</p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase ${badgeStyle}">${badgeText}</span>
                            <div class="flex">${editTaskControls}</div>
                        </div>
                    </div>

                    ${task.description ? `<p class="text-[11px] text-slate-500 leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-150">${task.description}</p>` : ""}

                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 pt-2 border-t border-slate-100">
                        ${assignedGridHTML}
                        <div class="flex items-center gap-3 w-full sm:w-auto shrink-0 justify-end">
                            <span class="text-slate-500 text-[10px] font-bold">Kế hoạch:</span>
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-slate-900 text-xs">${progressVal}%</span>
                                <div class="w-24 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-100">
                                    <div class="bg-indigo-600 h-full rounded-full" style="width: ${progressVal}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Task-specific Vertical Progress Logs (Timeline) -->
                    ${(() => {
                      const taskLogs = cacheLogs.filter(
                        (l) => l.task_id === task.id || l.taskId === task.id
                      );
                      if (taskLogs.length === 0) {
                        return `
                                <div class="mt-3 pt-3 border-t border-slate-100">
                                    <p class="text-[10px] text-slate-400 italic">Chưa có nhật ký báo cáo tiến độ nào được đăng tải.</p>
                                </div>
                            `;
                      }
                      return `
                            <div class="mt-4 pt-4 border-t border-slate-100">
                                <h4 class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-1.5 no-print">
                                    <i data-lucide="history" class="w-3.5 h-3.5 text-indigo-500"></i>
                                    Nhật lý báo cáo Timeline (${taskLogs.length} lần cập nhật)
                                </h4>
                                <div class="relative pl-3 border-l-2 border-slate-150 space-y-3.5 my-2">
                                    ${taskLogs
                                      .map((log) => {
                                        let logStatusBadge = "";
                                        let dotColor = "bg-slate-300";
                                        if (log.status === "approved") {
                                          dotColor =
                                            log.auto_approved ||
                                            log.autoApproved
                                              ? "bg-amber-500"
                                              : "bg-emerald-500";
                                          logStatusBadge = `
                                                <span class="text-[8px] px-1 py-0.5 bg-emerald-50 text-emerald-700 rounded border border-emerald-100 font-bold uppercase tracking-wider">
                                                    ${log.auto_approved || log.autoApproved ? "Hệ thống duyệt" : "Đã duyệt"}
                                                </span>`;
                                        } else if (log.status === "pending") {
                                          dotColor =
                                            "bg-amber-400 animate-pulse";
                                          logStatusBadge =
                                            '<span class="text-[8px] px-1 py-0.5 bg-amber-50 text-amber-700 rounded border border-amber-150 font-bold uppercase tracking-wider animate-pulse">Chờ duyệt</span>';
                                        } else {
                                          dotColor = "bg-rose-500";
                                          logStatusBadge =
                                            '<span class="text-[8px] px-1 py-0.5 bg-rose-50 text-rose-700 rounded border border-rose-100 font-bold uppercase tracking-wider">Từ chối</span>';
                                        }

                                        return `
                                             <div class="relative group text-xs">
                                                 <!-- Timeline Dot -->
                                                 <div class="absolute -left-[19px] top-1 w-2.5 h-2.5 rounded-full ${dotColor} border-2 border-white shadow-xs transition-colors group-hover:scale-125"></div>
                                                 
                                                 <div class="bg-slate-50/70 hover:bg-slate-50 p-2.5 rounded-xl border border-slate-150/40 transition-all space-y-1.5">
                                                     <div class="flex flex-wrap items-center justify-between gap-1.5">
                                                         <div class="flex items-center gap-1.5 align-middle">
                                                             <img src="${log.user_avatar || "https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=250"}" 
                                                               class="w-8 h-8 rounded-full object-cover border border-slate-200" title="${log.user_name}">
                                                             <span class="font-bold text-slate-800">${log.user_name}</span>
                                                             <span class="text-[9px] text-slate-400 font-mono">${log.date}</span>
                                                         </div>
                                                         <div class="flex items-center gap-1.5">
                                                             <span class="font-bold text-slate-900">Đạt: ${log.progress_percent || log.progressPercent || 0}%</span>
                                                             ${logStatusBadge}
                                                         </div>
                                                     </div>
                                                     <p class="text-slate-600 font-normal leading-relaxed text-[11px]">${log.notes}</p>
                                                     ${renderTimelineAttachment(log)}
                                                 </div>
                                             </div>
                                        `;
                                      })
                                      .join("")}
                                </div>
                            </div>
                        `;
                    })()}
                `;
    container.appendChild(div);
  });
  lucide.createIcons();
}

function populateWorkersSelections() {
  const container = document.getElementById("workers-selection-grid");
  if (!container) return;
  container.innerHTML = "";

  const subStaff = cacheStaff.filter((u) => u.role === "staff");
  if (subStaff.length === 0) {
    container.innerHTML = `<span class="text-slate-400 text-xs italic">Hãy thêm hồ sơ nhân viên trước.</span>`;
    return;
  }

  subStaff.forEach((user) => {
    const label = document.createElement("label");
    label.className =
      "flex items-center gap-2 bg-white hover:bg-slate-50 border border-slate-150 p-2 rounded-xl cursor-pointer select-none transition-all";
    label.innerHTML = `
                    <input type="checkbox" name="assignees" value="${user.id}" class="rounded text-slate-900 focus:ring-0">
                    <img src="${user.avatar}" alt="Avatar" class="w-5 h-5 rounded-full object-cover">
                    <span>${user.name} <small class="text-slate-400">(${user.position_name || "Nhân viên"})</small></span>
                `;
    container.appendChild(label);
  });
}

function openTaskModal() {
  document.getElementById("task-modal").classList.remove("hidden");
  document.getElementById("task-modal-title").innerText =
    "Tạo Việc Mới & Chỉ Định Nhân Viên";
  document.getElementById("task-edit-id").value = "";
  document.getElementById("task-form").reset();

  // Uncheck assignees
  document
    .querySelectorAll('input[name="assignees"]')
    .forEach((cb) => (cb.checked = false));
}

function closeTaskModal() {
  document.getElementById("task-modal").classList.add("hidden");
}

async function handleTaskSubmit(event) {
  event.preventDefault();
  const editId = document.getElementById("task-edit-id").value;
  const title = document.getElementById("task-title").value.trim();
  const job_category_id = document.getElementById("task-job-category-id").value;
  const start_date = document.getElementById("task-start-date").value;
  const end_date = document.getElementById("task-end-date").value;
  const description = document.getElementById("task-description").value.trim();

  const assigned_users = [];
  document.querySelectorAll('input[name="assignees"]:checked').forEach((cb) => {
    assigned_users.push(cb.value);
  });

  const bodyData = {
    title,
    job_category_id,
    start_date,
    end_date,
    description,
    assigned_users,
  };

  try {
    let url = DASHBOARD_CONFIG.api.tasks;
    let method = "POST";

    if (editId) {
      url = DASHBOARD_CONFIG.api.tasks + "/" + editId;
      method = "PUT";
    }

    const response = await fetch(url, {
      method: method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(bodyData),
    });

    if (response.ok) {
      showToast(
        "success",
        "Kế hoạch cập nhật",
        editId
          ? "Đã lưu chỉnh sửa công việc!"
          : "Phân công công việc thành công."
      );
      closeTaskModal();
      syncData();
    } else {
      showToast(
        "alarm",
        "Phân việc lỗi",
        "Vui lòng hoàn tất đúng ngày bắt đầu và kết thúc."
      );
    }
  } catch (err) {
    console.error(err);
  }
}

function editTask(taskId) {
  const task = cacheTasks.find((t) => t.id === taskId);
  if (!task) return;

  openTaskModal();
  document.getElementById("task-modal-title").innerText =
    "Cập nhật công việc: " + task.title;
  document.getElementById("task-edit-id").value = task.id;

  document.getElementById("task-title").value = task.title;
  document.getElementById("task-job-category-id").value =
    task.job_category_id || "";
  document.getElementById("task-status").value = task.status;
  document.getElementById("task-start-date").value = task.start_date;
  document.getElementById("task-end-date").value = task.end_date;
  document.getElementById("task-description").value = task.description || "";

  // Check assignees checkboxes in modal
  const assignedIds = (task.assigned_users || []).map((u) => u.id);
  document.querySelectorAll('input[name="assignees"]').forEach((cb) => {
    cb.checked = assignedIds.includes(cb.value);
  });
}

async function deleteTask(taskId) {
  if (
    !confirm(
      "Bạn có chắc muốn hủy bỏ đầu việc này? Việc này sẽ gỡ nhân sự đã phân công."
    )
  )
    return;

  try {
    const response = await fetch(DASHBOARD_CONFIG.api.tasks + "/" + taskId, {
      method: "DELETE",
    });

    if (response.ok) {
      showToast(
        "success",
        "Đã gỡ dự án",
        "Tải trọng công việc được giải tỏa thành công."
      );
      syncData();
    }
  } catch (err) {
    console.error(err);
  }
}

// ----------------------------------------------------
// DYNAMIC VIEWPORT 5: REVIEWS PROGRESS FEED & DISPATCH
// ----------------------------------------------------
function populateLogTaskFilterOptions() {
  const select = document.getElementById("filter-log-task-id");
  if (!select) return;

  const currentValue = select.value || "";
  const taskOptions = cacheTasks
    .slice()
    .sort((a, b) => String(a.title || "").localeCompare(String(b.title || "")))
    .map(
      (task) =>
        `<option value="${task.id}">${task.title} [${task.job_category_name || "Chung"}]</option>`
    )
    .join("");

  select.innerHTML = `<option value="">Tất cả công việc</option>${taskOptions}`;
  if (
    currentValue &&
    cacheTasks.some((t) => String(t.id) === String(currentValue))
  ) {
    select.value = currentValue;
  }
}

function getFilteredProgressLogs() {
  const selectedTaskId = (
    document.getElementById("filter-log-task-id")?.value || ""
  ).trim();
  const keyword = (document.getElementById("filter-log-keyword")?.value || "")
    .toLowerCase()
    .trim();
  const categoryId = (
    document.getElementById("filter-log-category")?.value || ""
  ).trim();
  const filterStatus = (
    document.getElementById("filter-log-status")?.value || ""
  ).trim();
  const fromDate = (
    document.getElementById("filter-log-from")?.value || ""
  ).trim();
  const toDate = (document.getElementById("filter-log-to")?.value || "").trim();
  const selectedTaskIdNormalized = selectedTaskId.toLowerCase();
  const selectedTask = cacheTasks.find(
    (t) => String(t.id || "").toLowerCase() === selectedTaskIdNormalized
  );
  const selectedTaskTitleNormalized = String(selectedTask?.title || "")
    .toLowerCase()
    .trim();

  return cacheLogs.filter((log) => {
    const logStatus = String(log.status || "");
    const logDate = String(log.date || "");
    const rawTaskId =
      log.task_id ?? log.taskId ?? log.taskID ?? log.task?.id ?? "";
    const taskId = String(rawTaskId || "").trim();
    const taskIdNormalized = taskId.toLowerCase();
    const task = cacheTasks.find((t) => String(t.id) === taskId) || {};
    const taskCategoryId = String(task.job_category_id || "");
    const logTaskTitleNormalized = String(log.task_title || "")
      .toLowerCase()
      .trim();

    const searchText = [
      log.user_name,
      log.task_title,
      log.notes,
      task.job_category_name,
    ]
      .map((x) => String(x || "").toLowerCase())
      .join(" ");

    if (selectedTaskId) {
      const matchedById =
        taskIdNormalized && taskIdNormalized === selectedTaskIdNormalized;
      const matchedByTitle =
        !matchedById &&
        !!selectedTaskTitleNormalized &&
        logTaskTitleNormalized === selectedTaskTitleNormalized;

      if (!matchedById && !matchedByTitle) {
        return false;
      }
    }

    if (filterStatus && logStatus !== filterStatus) {
      return false;
    }

    if (keyword && !searchText.includes(keyword)) {
      return false;
    }

    if (categoryId && taskCategoryId !== categoryId) {
      return false;
    }

    if (fromDate && (!logDate || logDate < fromDate)) {
      return false;
    }

    if (toDate && (!logDate || logDate > toDate)) {
      return false;
    }

    return true;
  });
}

function clearLogFilters() {
  const ids = [
    "filter-log-task-id",
    "filter-log-keyword",
    "filter-log-category",
    "filter-log-status",
    "filter-log-from",
    "filter-log-to",
  ];

  ids.forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.value = "";
  });

  renderProgressLogs();
}

function renderProgressLogs() {
  const container = document.getElementById("logs-feed-container");
  container.innerHTML = "";

  const displayLogs = getFilteredProgressLogs();

  if (displayLogs.length === 0) {
    container.innerHTML = `
                    <div class="text-center py-12 bg-white border border-slate-200 rounded-2xl italic text-slate-400 text-xs">
                        Chưa có báo cáo nào phù hợp bộ lọc này.
                    </div>
                `;
    return;
  }

  displayLogs.forEach((log) => {
    const progressValue = Number(
      log.progress_percent ?? log.progressPercent ?? 0
    );
    const clampedProgress = Math.max(0, Math.min(100, progressValue));
    let progressBadgeClass =
      "bg-rose-50 border-rose-200 text-rose-700 ring-1 ring-rose-100";
    let progressBarClass = "bg-rose-500";

    if (clampedProgress >= 100) {
      progressBadgeClass =
        "bg-emerald-50 border-emerald-200 text-emerald-700 ring-1 ring-emerald-100";
      progressBarClass = "bg-emerald-500";
    } else if (clampedProgress >= 60) {
      progressBadgeClass =
        "bg-blue-50 border-blue-200 text-blue-700 ring-1 ring-blue-100";
      progressBarClass = "bg-blue-500";
    } else if (clampedProgress >= 30) {
      progressBadgeClass =
        "bg-amber-50 border-amber-200 text-amber-700 ring-1 ring-amber-100";
      progressBarClass = "bg-amber-500";
    }

    // Status mapping
    let statusBadge = "bg-amber-100 text-amber-800 border-amber-200";
    let statusName = "Chờ duyệt";
    if (log.status === "approved") {
      statusBadge =
        log.auto_approved == 1
          ? "bg-slate-100 text-slate-500 border-slate-200"
          : "bg-emerald-100 text-emerald-800 border-emerald-200";
      statusName =
        log.auto_approved == 1
          ? "Auto Approved (Hết ngày)"
          : "Chất lượng đạt chuẩn";
    } else if (log.status === "rejected") {
      statusBadge = "bg-rose-100 text-rose-800 border-rose-200";
      statusName = "Đã từ chối (Gọt lỗi)";
    }

    // Render Verification buttons if current user is admin/manager
    let reviewActions = "";
    if (PHP_CURRENT_USER.role !== "staff" && log.status === "pending") {
      reviewActions = `
                        <div class="flex items-center gap-1.5 shrink-0 pt-2 border-t border-slate-100 no-print">
                            <button onclick="approveLog(${log.id}, 'approved')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-bold cursor-pointer flex items-center gap-1 shadow-sm transition-all">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Chuẩn Y Đạt
                            </button>
                            <button onclick="approveLog(${log.id}, 'rejected')" class="px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-[10px] font-bold cursor-pointer flex items-center gap-1 shadow-sm transition-all">
                                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Từ chối (Làm lại)
                            </button>
                        </div>
                    `;
    }

    const card = document.createElement("div");
    card.className =
      "bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm flex flex-col md:flex-row";
    card.innerHTML = `
                    ${renderReviewAttachment(log, clampedProgress)}

                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex items-center gap-2">
                                    <img src="${log.user_avatar}" alt="Avatar" class="w-7 h-7 rounded-full object-cover border border-slate-100 shrink-0">
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-xs">${log.user_name}</h4>
                                        <span class="text-[9px] text-slate-400 block">${log.date}</span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-md border text-[9px] font-bold uppercase shrink-0 ${statusBadge}">${statusName}</span>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2">
                              <p class="text-[11px] font-extrabold text-slate-500">Công việc: <span class="text-slate-800">${log.task_title}</span></p>
                              <span class="inline-flex items-center px-2.5 py-1 rounded-md border text-[10px] font-extrabold ${progressBadgeClass}">Tiến độ: ${clampedProgress}%</span>
                            </div>
                            <div class="space-y-1">
                              <div class="w-full h-2 rounded-full bg-slate-100 border border-slate-200 overflow-hidden">
                                <div class="h-full ${progressBarClass} transition-all duration-300" style="width: ${clampedProgress}%"></div>
                              </div>
                              <p class="text-[10px] font-semibold text-slate-500">Mức hoàn thiện hiện tại</p>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-150">${log.notes}</p>
                            
                            ${
                              log.status === "approved" && log.approver_name
                                ? `
                                <div class="text-[9px] text-slate-400 flex items-center gap-1 pt-1 font-semibold">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                                    <span>Đã được kiểm định bởi: <strong class="text-slate-500">${log.approver_name}</strong></span>
                                </div>
                            `
                                : ""
                            }
                        </div>

                        ${reviewActions}
                    </div>
                `;
    container.appendChild(card);
  });
  lucide.createIcons();
}

// Only staff can submit daily logs
function populateWorkerTaskSelect() {
  const select = document.getElementById("log-task-id");
  if (!select) return;
  select.innerHTML = "";

  // Render option of tasks
  if (cacheTasks.length === 0) {
    select.innerHTML = `<option value="">Không có việc giao nào khả thi...</option>`;
    return;
  }

  cacheTasks.forEach((task) => {
    select.innerHTML += `<option value="${task.id}">${task.title} [${task.job_category_name || "Chung"}]</option>`;
  });

  select.onchange = () => syncLogProgressConstraint(select.value);
}

function getLatestOwnProgressForTask(taskId, dateLimit = null) {
  const taskLogs = cacheLogs
    .filter((log) => {
      const isSameTask = log.task_id === taskId || log.taskId === taskId;
      const isOwnLog =
        log.user_id === PHP_CURRENT_USER.id ||
        log.userId === PHP_CURRENT_USER.id;
      const isBeforeSelectedDate =
        !dateLimit || String(log.date || "") < String(dateLimit);
      return isSameTask && isOwnLog && isBeforeSelectedDate;
    })
    .sort((a, b) => {
      const dateDiff = String(b.date || "").localeCompare(String(a.date || ""));
      if (dateDiff !== 0) return dateDiff;
      return Number(b.id || 0) - Number(a.id || 0);
    });

  return taskLogs[0] || null;
}

function setLogProgressFeedback(message = "", tone = "info") {
  const feedback = document.getElementById("log-progress-feedback");
  if (!feedback) return;

  if (!message) {
    feedback.className = "hidden rounded-xl border px-3 py-2 text-[11px]";
    feedback.textContent = "";
    return;
  }

  if (tone === "error") {
    feedback.className =
      "rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-3 py-2 text-[11px]";
  } else {
    feedback.className =
      "rounded-xl border border-amber-200 bg-amber-50 text-amber-700 px-3 py-2 text-[11px]";
  }

  feedback.textContent = message;
}

function syncLogProgressConstraint(taskId) {
  const slider = document.getElementById("log-progress-slider");
  const label = document.getElementById("slider-val-lbl");
  const dateInput = document.getElementById("log-date");
  if (!slider || !label) return;

  const latestLog = taskId
    ? getLatestOwnProgressForTask(taskId, dateInput ? dateInput.value : null)
    : null;
  const minValue = latestLog
    ? Number(latestLog.progress_percent || latestLog.progressPercent || 0)
    : 0;

  slider.min = String(minValue);
  if (Number(slider.value) < minValue) {
    slider.value = String(minValue);
  }

  label.innerText = slider.value + "%";
  slider.title = latestLog
    ? `Mức tối thiểu cho ngày sau: ${minValue}%`
    : "Mức tối thiểu hiện tại: 0%";

  if (latestLog) {
    const targetDate =
      dateInput && dateInput.value ? dateInput.value : "ngày đã chọn";
    setLogProgressFeedback(
      `Tiến độ cho ${targetDate} phải >= ${minValue}% (mốc trước: ${latestLog.date}).`,
      "info"
    );
  } else {
    setLogProgressFeedback("");
  }
}

// --- PROFILE EDIT CONTROLLERS ---
function openProfileModal() {
  document.getElementById("profile-modal").classList.remove("hidden");

  // Populate form fields from current user session data
  document.getElementById("profile-name").value = PHP_CURRENT_USER.name || "";
  document.getElementById("profile-dob").value = PHP_CURRENT_USER.dob || "";
  document.getElementById("profile-address").value =
    PHP_CURRENT_USER.address || "";
  document.getElementById("profile-ic").value =
    PHP_CURRENT_USER.identity_card || "";
  document.getElementById("profile-phone").value = PHP_CURRENT_USER.phone || "";
  document.getElementById("profile-password").value = ""; // clear password input field
  document.getElementById("profile-avatar-preview").src =
    PHP_CURRENT_USER.avatar ||
    "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150";

  // Reset base64 variable
  profileAvatarBase64 = null;
}

function closeProfileModal() {
  document.getElementById("profile-modal").classList.add("hidden");
}

let profileAvatarBase64 = null;

function previewProfileAvatar(event) {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function (e) {
    const img = new Image();
    img.onload = function () {
      // Create canvas for downscaling (avatar is small, max 160x160 is perfect)
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d");

      const maxDim = 160;
      let width = img.width;
      let height = img.height;

      if (width > height) {
        if (width > maxDim) {
          height = Math.round((height * maxDim) / width);
          width = maxDim;
        }
      } else {
        if (height > maxDim) {
          width = Math.round((width * maxDim) / height);
          height = maxDim;
        }
      }

      canvas.width = width;
      canvas.height = height;
      ctx.drawImage(img, 0, 0, width, height);

      const compressedBase64 = canvas.toDataURL("image/jpeg", 0.85);
      document.getElementById("profile-avatar-preview").src = compressedBase64;
      profileAvatarBase64 = compressedBase64;
    };
    img.src = e.target.result;
  };
  reader.readAsDataURL(file);
}

async function handleProfileSubmit(event) {
  event.preventDefault();
  const btnSubmit = document.getElementById("btn-profile-submit");
  btnSubmit.disabled = true;
  btnSubmit.innerHTML =
    '<span>Đang lưu...</span> <div class="animate-spin rounded-full h-3 w-3 border-2 border-white/30 border-t-white inline-block ml-1"></div>';

  const name = document.getElementById("profile-name").value.trim();
  const dob = document.getElementById("profile-dob").value;
  const address = document.getElementById("profile-address").value.trim();
  const identity_card = document.getElementById("profile-ic").value.trim();
  const phone = document.getElementById("profile-phone").value.trim();
  const password = document.getElementById("profile-password").value;

  const bodyData = {
    name,
    dob,
    address,
    identity_card,
    phone,
  };

  if (password) {
    bodyData.password = password;
  }

  if (profileAvatarBase64) {
    bodyData.avatar = profileAvatarBase64;
  }

  try {
    const response = await fetch(
      DASHBOARD_CONFIG.api.users + "/" + PHP_CURRENT_USER.id,
      {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(bodyData),
      }
    );

    const result = await response.json();

    if (response.ok && (response.status === 200 || response.status === 201)) {
      showToast(
        "success",
        "Cập nhật thành công",
        "Thông tin hồ sơ cá nhân đã được lưu trữ thành công!"
      );

      const updatedUser = {
        ...PHP_CURRENT_USER,
        ...bodyData,
        ...(profileAvatarBase64 ? { avatar: profileAvatarBase64 } : {}),
      };

      try {
        localStorage.setItem("moc_viet_user", JSON.stringify(updatedUser));
      } catch (e) {
        console.warn(
          "Storage quota exceeded, skipping local storage cache.",
          e
        );
      }

      closeProfileModal();
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } else {
      showToast(
        "alarm",
        "Thất bại",
        result.error || "Gặp lỗi trong quá trình lưu hồ sơ."
      );
    }
  } catch (err) {
    console.error(err);
    showToast(
      "alarm",
      "Lỗi kết nối",
      "Không thể liên kết với máy chủ để lưu thông tin."
    );
  } finally {
    btnSubmit.disabled = false;
    btnSubmit.innerHTML = "Lưu hồ sơ";
  }
}

function openSubmitLogModal() {
  document.getElementById("submit-log-modal").classList.remove("hidden");
  document.getElementById("submit-log-form").reset();
  document
    .getElementById("image-upload-preview-container")
    .classList.add("hidden");
  const previewImage = document.getElementById("image-upload-preview");
  const previewName = document.getElementById("file-upload-name");
  if (previewImage) previewImage.classList.remove("hidden");
  if (previewName) {
    previewName.classList.add("hidden");
    previewName.textContent = "";
  }
  document.getElementById("slider-val-lbl").innerText = "50%";
  document.getElementById("log-progress-slider").min = "0";
  setLogProgressFeedback("");

  const taskSelect = document.getElementById("log-task-id");
  const dateInput = document.getElementById("log-date");
  const slider = document.getElementById("log-progress-slider");

  const reevaluateConstraint = () =>
    syncLogProgressConstraint(taskSelect ? taskSelect.value : "");

  reevaluateConstraint();

  if (dateInput) {
    dateInput.onchange = reevaluateConstraint;
  }

  if (slider) {
    slider.oninput = () => {
      document.getElementById("slider-val-lbl").innerText = slider.value + "%";
      reevaluateConstraint();
    };
  }

  logAttachmentFile = null;
}
function closeSubmitLogModal() {
  document.getElementById("submit-log-modal").classList.add("hidden");
}

let logAttachmentFile = null;

function previewSelectedImage(event) {
  const file = event.target.files[0];
  if (!file) return;

  logAttachmentFile = file;

  const preview = document.getElementById("image-upload-preview");
  const container = document.getElementById("image-upload-preview-container");
  const fileName = document.getElementById("file-upload-name");
  if (!container) return;
  container.classList.remove("hidden");

  if (file.type.startsWith("image/")) {
    const reader = new FileReader();
    reader.onload = function (e) {
      if (preview) {
        preview.src = e.target.result;
        preview.classList.remove("hidden");
      }
      if (fileName) {
        fileName.classList.add("hidden");
        fileName.textContent = "";
      }
    };
    reader.readAsDataURL(file);
    return;
  }

  if (preview) {
    preview.classList.add("hidden");
    preview.removeAttribute("src");
  }
  if (fileName) {
    const ext = (file.name.split(".").pop() || "FILE").toUpperCase();
    fileName.textContent = `Da chon tep: ${file.name} (${ext})`;
    fileName.classList.remove("hidden");
  }
}

async function handleLogSubmit(event) {
  event.preventDefault();
  const btnSubmit = document.getElementById("btn-log-submit");
  btnSubmit.disabled = true;
  btnSubmit.innerText = "ĐANG GỬI BÁO CÁO...";

  const task_id = document.getElementById("log-task-id").value;
  const progress_percent = Number(
    document.getElementById("log-progress-slider").value
  );
  const date = document.getElementById("log-date").value;
  const notes = document.getElementById("log-notes").value.trim();
  const latestLog = task_id ? getLatestOwnProgressForTask(task_id, date) : null;
  const latestProgress = latestLog
    ? Number(latestLog.progress_percent || latestLog.progressPercent || 0)
    : 0;

  if (latestLog && progress_percent < latestProgress) {
    setLogProgressFeedback(
      `Tiến độ của ngày sau phải lớn hơn hoặc bằng ngày trước. Mức trước đó là ${latestProgress}%.`,
      "error"
    );
    document.getElementById("log-progress-slider").focus();
    btnSubmit.disabled = false;
    btnSubmit.innerText = "Nộp báo cáo";
    return;
  }

  const formData = new FormData();
  formData.append("task_id", task_id);
  formData.append("user_id", PHP_CURRENT_USER.id || "");
  formData.append("progress_percent", String(progress_percent));
  formData.append("date", date);
  formData.append("notes", notes);

  if (logAttachmentFile) {
    formData.append("attachment", logAttachmentFile);
  }

  try {
    const response = await fetch(DASHBOARD_CONFIG.api.logs, {
      method: "POST",
      body: formData,
    });

    const r = await response.json().catch(() => ({}));

    if (response.ok) {
      showToast(
        "success",
        "Nộp thành công",
        "Báo cáo công việc đã gửi lên máy chủ thành công."
      );
      setLogProgressFeedback("");
      closeSubmitLogModal();
      syncData();
    } else {
      const backendMessage =
        r.messages?.error || r.message || r.error || "Gửi báo cáo thất bại";
      setLogProgressFeedback(String(backendMessage), "error");

      if (String(backendMessage).includes("Tiến độ")) {
        document.getElementById("log-progress-slider").focus();
      } else {
        showToast("alarm", "Lỗi dữ liệu", String(backendMessage));
      }
    }
  } catch (err) {
    console.error(err);
    showToast("alarm", "Lỗi kết nối", "Không thể gửi dữ liệu báo cáo.");
  } finally {
    btnSubmit.disabled = false;
    btnSubmit.innerText = "Nộp báo cáo";
  }
}

async function approveLog(logId, status) {
  try {
    const response = await fetch(
      DASHBOARD_CONFIG.api.logsApprove + "/" + logId,
      {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          status: status,
          approved_by: PHP_CURRENT_USER.id,
        }),
      }
    );

    if (response.ok) {
      showToast(
        "success",
        "Cập nhật chất lượng",
        status === "approved"
          ? "Chuẩn y điểm năng lượng đạt tiêu chuẩn thẫm mỹ gỗ!"
          : "Mẫu báo cáo đã bị bác bỏ hoàn thiện"
      );
      syncData();
    } else {
      showToast("alarm", "Lỗi duyệt", "Gặp trục trặc khi phê duyệt báo cáo.");
    }
  } catch (err) {
    console.error(err);
  }
}

// ----------------------------------------------------
// DYNAMIC VIEWPORT 6: PERFORMANCE SUMMARY (REPORTS)
// ----------------------------------------------------
function renderPerformanceReports() {
  if (!cacheStats) return;

  // Productivity leaderboard
  const tbody = document.getElementById("performance-table-body");
  tbody.innerHTML = "";

  const productivity = cacheStats.employeeProductivity || [];

  if (productivity.length === 0) {
    tbody.innerHTML = `<tr><td colSpan="4" class="py-6 text-center text-slate-400 italic">Chưa phát hiện dữ liệu hoạt động tích lũy</td></tr>`;
  } else {
    productivity.forEach((emp, index) => {
      let rating = "Nhân viên khá";
      let ratingStyle = "bg-blue-50 text-blue-700";
      if (emp.totalProgressPoints >= 105) {
        rating = "Hiệu suất cao";
        ratingStyle = "bg-emerald-50 text-emerald-700";
      } else if (emp.totalProgressPoints < 40) {
        rating = "Mức cơ bản";
        ratingStyle = "bg-rose-50 text-rose-700";
      }

      const tr = document.createElement("tr");
      tr.className = "hover:bg-slate-50/50 transition-colors";
      tr.innerHTML = `
                        <td class="py-3 px-3 flex items-center gap-2">
                            <span class="font-extrabold text-[11px] text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded mr-1">${index + 1}</span>
                            <img src="${emp.avatar}" alt="Avatar" class="w-7 h-7 rounded-full object-cover">
                            <div>
                                <span class="block">${emp.name}</span>
                                <span class="text-[8px] font-mono px-1 rounded ${ratingStyle}">${rating}</span>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-center text-slate-700 font-bold">${emp.assignedTasksCount} dự án</td>
                        <td class="py-3 px-3 text-center text-slate-500">${emp.approvedLogsCount} lượt báo cáo</td>
                        <td class="py-3 px-3 text-right text-slate-950 font-mono font-bold">${emp.totalProgressPoints}% IDP</td>
                    `;
      tbody.appendChild(tr);
    });
  }

  // Tasks progress summary cards
  const tContainer = document.getElementById("report-tasks-list-container");
  tContainer.innerHTML = "";

  if (cacheTasks.length === 0) {
    tContainer.innerHTML = `<p class="text-xs text-slate-400 italic text-center py-6">Không có nhiệm vụ bàn giao gỗ.</p>`;
    return;
  }

  cacheTasks.forEach((task) => {
    const approvedLogs = cacheLogs.filter(
      (l) => l.task_id === task.id && l.status === "approved"
    );
    const progressLevel =
      approvedLogs.length === 0
        ? 0
        : Math.max(...approvedLogs.map((l) => parseInt(l.progress_percent)));

    const card = document.createElement("div");
    card.className =
      "space-y-1.5 p-3.5 bg-slate-50 border border-slate-150 rounded-xl hover:bg-slate-100/50 transition-all";
    card.innerHTML = `
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <span class="text-slate-800 font-bold text-xs">${task.title}</span>
                            <span class="block text-[9px] text-slate-400 font-mono">${task.start_date} ~ ${task.end_date}</span>
                        </div>
                        <span class="text-[9px] bg-slate-200 text-slate-800 font-bold uppercase rounded px-1">${task.status === "completed" ? "Hoàn thiện" : "Chạy ráp"}</span>
                    </div>
                    <div class="space-y-1 pt-1">
                        <div class="flex justify-between text-[9px]">
                            <span class="text-slate-500 font-semibold">Tỷ lệ tiến trình:</span>
                            <span class="font-mono text-slate-900 font-bold">${progressLevel}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-1 overflow-hidden">
                            <div class="h-full bg-indigo-600 rounded-full" style="width: ${progressLevel}%"></div>
                        </div>
                    </div>
                `;
    tContainer.appendChild(card);
  });
}

// ----------------------------------------------------
// DYNAMIC ALARM TOAST MESSAGE BIN
// ----------------------------------------------------
function showToast(type, title, message) {
  const btn = document.getElementById("toast-bin");
  const icon = document.getElementById("toast-icon");
  const titEl = document.getElementById("toast-title");
  const descEl = document.getElementById("toast-desc");

  btn.classList.remove("hidden");
  titEl.innerText = title;
  descEl.innerText = message;

  if (type === "success") {
    btn.className =
      "mb-6 p-4 rounded-xl border flex items-start gap-3 text-xs shadow-md bg-emerald-50 border-emerald-100 text-emerald-900";
    icon.setAttribute("data-lucide", "check-circle-2");
  } else {
    btn.className =
      "mb-6 p-4 rounded-xl border flex items-start gap-3 text-xs shadow-md bg-rose-50 border-rose-100 text-rose-900";
    icon.setAttribute("data-lucide", "alert-circle");
  }
  lucide.createIcons();

  // Auto close after 3 seconds
  setTimeout(() => {
    closeToast();
  }, 3500);
}

function closeToast() {
  document.getElementById("toast-bin").classList.add("hidden");
}

// ----------------------------------------------------
// SYSTEM MASTER CATEGORIES CRUD & MANAGEMENT
// ----------------------------------------------------
function renderMasterCategories() {
  // Render Positions
  const posContainer = document.getElementById("list-positions");
  if (posContainer) {
    posContainer.innerHTML = "";
    if (cachePositions.length === 0) {
      posContainer.innerHTML = `<div class="text-slate-400 text-xs italic py-4 text-center">Trống.</div>`;
    } else {
      cachePositions.forEach((pos) => {
        const isEditable =
          PHP_CURRENT_USER.role === "admin" ||
          (PHP_CURRENT_USER.custom_permissions &&
            PHP_CURRENT_USER.custom_permissions.includes("p5"));
        const actionButtons = isEditable
          ? `
                            <div class="flex gap-1 items-center shrink-0">
                                <button onclick="openCategoryModal('positions', '${pos.id}')" class="p-1.5 bg-indigo-50 hover:bg-indigo-100 rounded text-indigo-600 hover:text-indigo-800 transition-colors cursor-pointer" title="Sửa"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
                                <button onclick="deleteCategory('positions', '${pos.id}')" class="p-1.5 bg-rose-50 hover:bg-rose-100 rounded text-rose-600 hover:text-rose-800 transition-colors cursor-pointer" title="Xóa"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                            </div>
                        `
          : "";

        const item = document.createElement("div");
        item.className =
          "p-3 bg-slate-50 hover:bg-slate-100/80 border border-slate-150 rounded-xl space-y-1 relative group transition-colors flex justify-between items-center gap-3";
        item.innerHTML = `
                            <div class="space-y-0.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-slate-800 text-xs truncate">${pos.name}</h4>
                                    <span class="text-[9px] font-mono text-slate-400 bg-slate-200/50 px-1 rounded shrink-0">${pos.id}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 leading-relaxed truncate-2-lines">${pos.description || "Không có mô tả."}</p>
                            </div>
                            ${actionButtons}
                        `;
        posContainer.appendChild(item);
      });
    }
  }

  // Render Job Categories
  const jobsContainer = document.getElementById("list-job-categories");
  if (jobsContainer) {
    jobsContainer.innerHTML = "";
    if (cacheJobCategories.length === 0) {
      jobsContainer.innerHTML = `<div class="text-slate-400 text-xs italic py-4 text-center">Trống.</div>`;
    } else {
      cacheJobCategories.forEach((job) => {
        const isEditable =
          PHP_CURRENT_USER.role === "admin" ||
          (PHP_CURRENT_USER.custom_permissions &&
            PHP_CURRENT_USER.custom_permissions.includes("p5"));
        const actionButtons = isEditable
          ? `
                            <div class="flex gap-1 items-center shrink-0">
                                <button onclick="openCategoryModal('jobs', '${job.id}')" class="p-1.5 bg-blue-50 hover:bg-blue-100 rounded text-blue-600 hover:text-blue-800 transition-colors cursor-pointer" title="Sửa"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
                                <button onclick="deleteCategory('jobs', '${job.id}')" class="p-1.5 bg-rose-50 hover:bg-rose-100 rounded text-rose-600 hover:text-rose-800 transition-colors cursor-pointer" title="Xóa"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                            </div>
                        `
          : "";

        const item = document.createElement("div");
        item.className =
          "p-3 bg-slate-50 hover:bg-slate-100/80 border border-slate-150 rounded-xl space-y-1 relative group transition-colors flex justify-between items-center gap-3";
        item.innerHTML = `
                            <div class="space-y-0.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-slate-800 text-xs truncate">${job.name}</h4>
                                    <span class="text-[9px] font-mono text-slate-400 bg-slate-200/50 px-1 rounded shrink-0">${job.id}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 leading-relaxed truncate-2-lines">${job.description || "Không có mô tả."}</p>
                            </div>
                            ${actionButtons}
                        `;
        jobsContainer.appendChild(item);
      });
    }
  }

  // Render Permissions
  const permContainer = document.getElementById("list-permissions");
  if (permContainer) {
    permContainer.innerHTML = "";
    if (cachePermissions.length === 0) {
      permContainer.innerHTML = `<div class="text-slate-400 text-xs italic py-4 text-center">Trống.</div>`;
    } else {
      cachePermissions.forEach((perm) => {
        const isEditable =
          PHP_CURRENT_USER.role === "admin" ||
          (PHP_CURRENT_USER.custom_permissions &&
            PHP_CURRENT_USER.custom_permissions.includes("p5"));
        const actionButtons = isEditable
          ? `
                            <div class="flex gap-1 items-center shrink-0">
                                <button onclick="openCategoryModal('permissions', '${perm.id}')" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 rounded text-emerald-600 hover:text-emerald-800 transition-colors cursor-pointer" title="Sửa"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
                                <button onclick="deleteCategory('permissions', '${perm.id}')" class="p-1.5 bg-rose-50 hover:bg-rose-100 rounded text-rose-600 hover:text-rose-800 transition-colors cursor-pointer" title="Xóa"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                            </div>
                        `
          : "";

        const item = document.createElement("div");
        item.className =
          "p-3 bg-slate-50 hover:bg-slate-100/80 border border-slate-150 rounded-xl space-y-1 relative group transition-colors flex justify-between items-center gap-3";
        item.innerHTML = `
                            <div class="space-y-0.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-slate-800 text-xs truncate">${perm.name}</h4>
                                    <span class="text-[9px] font-mono text-slate-400 bg-slate-200/50 px-1 rounded shrink-0">${perm.id}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 leading-relaxed truncate-2-lines">${perm.description || "Không có mô tả."}</p>
                            </div>
                            ${actionButtons}
                        `;
        permContainer.appendChild(item);
      });
    }
  }
  lucide.createIcons();
}

function openCategoryModal(type, itemId = "") {
  document.getElementById("category-form").reset();
  document.getElementById("category-modal").classList.remove("hidden");
  document.getElementById("category-type").value = type;
  document.getElementById("category-edit-id").value = itemId;

  const idFieldWrapper = document.getElementById("category-id-field-wrapper");
  idFieldWrapper.classList.add("hidden");
  document.getElementById("category-item-id").required = false;

  let titlePrefix = "Khai báo";
  if (itemId) {
    titlePrefix = "Hiệu chỉnh";
  }

  let typeName = "Danh mục";
  if (type === "positions") {
    typeName = "Chức Danh Vị Trí";
  } else if (type === "jobs") {
    typeName = "Loại Hình Công Việc";
  } else if (type === "permissions") {
    typeName = "Quyền Hệ Thống";
    idFieldWrapper.classList.remove("hidden");
    document.getElementById("category-item-id").required = true;
  }

  document.getElementById("category-modal-title").innerText =
    `${titlePrefix} ${typeName}`;

  if (itemId) {
    let item = null;
    if (type === "positions")
      item = cachePositions.find((x) => x.id === itemId);
    else if (type === "jobs")
      item = cacheJobCategories.find((x) => x.id === itemId);
    else if (type === "permissions")
      item = cachePermissions.find((x) => x.id === itemId);

    if (item) {
      document.getElementById("category-name").value = item.name || "";
      document.getElementById("category-description").value =
        item.description || "";
      document.getElementById("category-item-id").value = item.id || "";
    }
  } else {
    document.getElementById("category-item-id").value = "";
  }
}

function closeCategoryModal() {
  document.getElementById("category-modal").classList.add("hidden");
}

async function handleCategorySubmit(event) {
  event.preventDefault();
  const type = document.getElementById("category-type").value;
  const editId = document.getElementById("category-edit-id").value;
  const name = document.getElementById("category-name").value.trim();
  const description = document
    .getElementById("category-description")
    .value.trim();
  const itemIdInput = document.getElementById("category-item-id").value.trim();

  const payload = { name, description };
  if (type === "permissions" && !editId) {
    payload.id = itemIdInput;
  }

  let url =
    `<?= base_url('api/categories') ?>/` + (type === "jobs" ? "jobs" : type);
  let method = "POST";

  if (editId) {
    method = "PUT";
    url += "/" + editId;
  }

  try {
    const res = await fetch(url, {
      method: method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    if (res.ok) {
      showToast("success", "Thành công", "Lưu thông tin danh mục thành công!");
      closeCategoryModal();
      syncData();
    } else {
      const err = await res.json();
      showToast(
        "danger",
        "Lỗi phân phối",
        err.error || "Thao tác danh mục thất bại."
      );
    }
  } catch (ex) {
    console.error(ex);
    showToast(
      "danger",
      "Lỗi đồng bộ",
      "Không thể gửi dữ liệu danh mục lên hệ thống."
    );
  }
}

async function deleteCategory(type, id) {
  if (
    !confirm(
      "Bạn có chắc chắn muốn xóa danh mục này? Thao tác có thể ảnh hưởng đến dữ liệu nhân viên/công việc liên quan."
    )
  )
    return;

  let url =
    `<?= base_url('api/categories') ?>/` +
    (type === "jobs" ? "jobs" : type) +
    "/" +
    id;

  try {
    const res = await fetch(url, { method: "DELETE" });
    if (res.ok) {
      showToast(
        "success",
        "Thành công",
        "Đã xóa danh mục ra khỏi danh sách hệ thống!"
      );
      syncData();
    } else {
      const err = await res.json();
      showToast(
        "danger",
        "Lỗi thao tác",
        err.error || "Không thể xóa mục danh mục này."
      );
    }
  } catch (ex) {
    console.error(ex);
    showToast("danger", "Lỗi kết nối", "Mất đường truyền mạng với server.");
  }
}
