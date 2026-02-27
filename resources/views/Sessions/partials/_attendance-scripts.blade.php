@vite(['resources/js/common/form-utils.js'])
@vite(['resources/js/common/table-dropdown.js'])
@vite(['resources/js/common/autocomplete-utils.js'])
@vite(['resources/css/autocomplete.css'])
@vite(['resources/js/pages/customer-attendance.js'])

<script>
  // Contact number formatting function
  function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length > 11) {
      value = value.substring(0, 11);
    }
    
    let formatted = '';
    if (value.length > 0) {
      formatted = value.substring(0, 4);
      if (value.length > 4) {
        formatted += '-' + value.substring(4, 7);
      }
      if (value.length > 7) {
        formatted += '-' + value.substring(7, 11);
      }
    }
    
    input.value = formatted;
  }
</script>
