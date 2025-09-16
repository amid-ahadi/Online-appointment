    </div> <!-- container -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('.pdate').pDatepicker({
        format: 'YYYY-MM-DD', // ← این خط حیاتیه!
        calendarType: 'persian',
        toolbox: {
            calendarSwitch: {
                enabled: true
            }
        },
        text: {
            'nextMonth': 'ماه بعد',
            'previousMonth': 'ماه قبل',
            'selectMonth': 'انتخاب ماه',
            'selectYear': 'انتخاب سال',
            'submit': 'تأیید',
            'cancel': 'انصراف'
        }
    });
});
</script>
   </body>
</html>