import $ from 'jquery';
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
    $(document).on(
        'click',
        '.dataTables_wrapper [data-link], .dataTables_wrapper [data-delete]',
        clickHandler
    );
    handlerInstalled = true;
}

export function renderActions(data) {
    return `<div class="d-inline-flex gap-1">
        ${
            data.edit_url
                ? `<a href="${data.edit_url}" data-link class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>`
                : ''
        }
        ${
            data.delete_url
                ? `<button data-delete="${data.delete_url}" type="button" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>`
                : ''
        }
    </div>`;
}

export function renderBoolean(value, trueLabel = null, falseLabel = null) {
    return value
        ? `<span class="badge bg-success">${trueLabel || trans('common.active')}</span>`
        : `<span class="badge bg-secondary">${falseLabel || trans('common.inactive')}</span>`;
}

export function renderMoney(value) {
    const n = Number(value || 0);
    return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
