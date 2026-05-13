import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';
import { trans } from '@/i18n';

let handlerInstalled = false;

function clickHandler(e) {
    const linkEl = e.target.closest('[data-link]');
    if (linkEl) {
        e.preventDefault();
        router.visit(linkEl.getAttribute('href'));
        return;
    }
    const delEl = e.target.closest('[data-delete]');
    if (delEl) {
        e.preventDefault();
        const url = delEl.getAttribute('data-delete');
        Swal.fire({
            title: trans('common.confirm_delete_title'),
            text: trans('common.confirm_delete_text'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: trans('common.confirm_yes_delete'),
            cancelButtonText: trans('common.cancel'),
        }).then((result) => {
            if (result.isConfirmed) {
                router.delete(url, { preserveScroll: true });
            }
        });
    }
}

export function installDataTableActions() {
    if (handlerInstalled) return;
    document.addEventListener('click', clickHandler);
    handlerInstalled = true;
}

export function renderActions(data) {
    return `<div class="d-inline-flex gap-1">
        ${
            data && data.edit_url
                ? `<a href="${data.edit_url}" data-link class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>`
                : ''
        }
        ${
            data && data.delete_url
                ? `<button data-delete="${data.delete_url}" type="button" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>`
                : ''
        }
    </div>`;
}

// DataTables calls render(data, type, row, meta). Only emit HTML for 'display' /
// 'filter' so that sort/type retain the raw value.
export function renderBoolean(value, type, _row, _meta, opts = {}) {
    // Allow direct invocation: renderBoolean(true) — treat 2nd arg as `type` only
    // when it's a DataTables type token (string in known set), otherwise fall
    // back to legacy two-arg form `renderBoolean(value, trueLabel, falseLabel)`.
    let trueLabel = opts.trueLabel;
    let falseLabel = opts.falseLabel;
    const isDtType = typeof type === 'string' && ['display', 'filter', 'sort', 'type', 'sp'].includes(type);
    if (!isDtType && typeof type === 'string') {
        // Legacy two-arg form used by callers like `renderBoolean(v, 'Yes', 'No')`.
        trueLabel = type;
        falseLabel = _row;
        type = 'display';
    }
    if (type && type !== 'display' && type !== 'filter') return value ? 1 : 0;

    const truthy = !!Number(value) || value === true || value === 'true';
    if (truthy) {
        return `<span class="badge bg-success">${trueLabel || trans('common.active')}</span>`;
    }
    return `<span class="badge bg-secondary">${falseLabel || trans('common.inactive')}</span>`;
}

export function renderMoney(value, type) {
    if (type && type !== 'display' && type !== 'filter') return Number(value || 0);
    const n = Number(value || 0);
    return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
