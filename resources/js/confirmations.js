const confirmDeletionForm = (e, form, text) => {
e.preventDefault();
     Swal.fire({
        title: "Are you sure?",
        html: `Are you sure you wish to delete this ${text}?<br>This is an irreversible action, all files will be removed.`,
        icon: 'warning',
        confirmButtonColor: '#d9534f',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!',
        reverseButtons: true
    }).then((result) => {
              if (result.isConfirmed) {
                  return form.submit()
              } else {
                  return Swal.fire('Canceled ...', `${capitalizeWord(text)} deletion has been canceled.`, 'info')
              }
          });    
}

function capitalizeWord(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}
