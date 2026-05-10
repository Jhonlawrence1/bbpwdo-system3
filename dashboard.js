function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('active');
    }

    function searchPWD() {
      const input = document.getElementById('searchInput');
      const filter = input.value.toUpperCase();
      const table = document.getElementById('pwdTable');
      const tr = table.getElementsByTagName('tr');
      let count = 0;

      for (let i = 1; i < tr.length; i++) {
        const tdName = tr[i].getElementsByTagName('td')[1];
        if (tdName) {
          const nameText = tdName.textContent || tdName.innerText;
          if (nameText.toUpperCase().indexOf(filter) > -1) {
            tr[i].style.display = '';
            count++;
          } else {
            tr[i].style.display = 'none';
          }
        }
      }
      document.getElementById('totalCount').textContent = 'Showing ' + count + ' records';
    }

    function filterPWD() {
      const statusFilter = document.getElementById('filterStatus').value;
      const table = document.getElementById('pwdTable');
      const tr = table.getElementsByTagName('tr');
      let count = 0;

      for (let i = 1; i < tr.length; i++) {
        const tdStatus = tr[i].getElementsByTagName('td')[4];
        if (tdStatus) {
          const statusText = tdStatus.textContent || tdStatus.innerText;
          if (statusFilter === '' || statusText.includes(statusFilter)) {
            tr[i].style.display = '';
            count++;
          } else {
            tr[i].style.display = 'none';
          }
        }
      }
      document.getElementById('totalCount').textContent = 'Showing ' + count + ' records';
    }

    function printPWD(btn) {
      const row = btn.closest('tr');
      const cells = row.getElementsByTagName('td');
      const id = cells[0].textContent;
      const name = cells[1].textContent.replace(/\n/g, ' ').trim();
      const disability = cells[2].textContent;
      const date = cells[3].textContent;
      const status = cells[4].textContent;

      const printWindow = window.open('', '_blank');
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>PWD ID - ${id}</title>
          <style>
            body { font-family: Arial, sans-serif; padding: 40px; }
            .card { border: 2px solid #1a1a2e; border-radius: 15px; padding: 30px; max-width: 400px; margin: 0 auto; }
            h2 { color: #e94560; text-align: center; margin-bottom: 20px; }
            .logo { text-align: center; font-size: 40px; margin-bottom: 20px; }
            .info { margin: 15px 0; }
            .label { font-weight: bold; color: #666; }
            .value { color: #1a1a2e; }
            .status { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 12px; }
            .verified { background: #d4edda; color: #155724; }
            .pending { background: #fff3cd; color: #856404; }
            .footer { text-align: center; margin-top: 30px; color: #999; font-size: 12px; }
          </style>
        </head>
        <body>
          <div class="card">
            <div class="logo">♿</div>
            <h2>PWD Center</h2>
            <div class="info"><span class="label">ID Number:</span> <span class="value">${id}</span></div>
            <div class="info"><span class="label">Name:</span> <span class="value">${name}</span></div>
            <div class="info"><span class="label">Type of Disability:</span> <span class="value">${disability}</span></div>
            <div class="info"><span class="label">Date Registered:</span> <span class="value">${date}</span></div>
            <div class="info"><span class="label">Status:</span> <span class="status ${status.toLowerCase().includes('verified') ? 'verified' : 'pending'}">${status}</span></div>
            <div class="footer">PWD Center - Persons With Disability Registration</div>
          </div>
          <script>window.print();<\/script>
        </body>
        </html>
      `);
printWindow.document.close();
    }

function logout() {
  localStorage.clear();
  window.location.href = 'login.html';
}

    function generateReport() {
      const table = document.getElementById('pwdTable');
      const rows = table.querySelectorAll('tbody tr');
      let data = [];
      
      rows.forEach(row => {
        if (row.style.display !== 'none') {
          const cells = row.getElementsByTagName('td');
          data.push({
            id: cells[0].textContent,
            name: cells[1].textContent.replace(/\n/g, ' ').trim(),
            disability: cells[2].textContent,
            date: cells[3].textContent,
            status: cells[4].textContent
          });
        }
      });

      const printWindow = window.open('', '_blank');
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>PWD Registration Report</title>
          <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            h1 { color: #1a1a2e; text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background: #e94560; color: white; }
            tr:nth-child(even) { background: #f9f9f9; }
            .footer { text-align: center; margin-top: 30px; color: #666; }
          </style>
        </head>
        <body>
          <h1>PWD Center - Registration Report</h1>
          <p>Generated: ${new Date().toLocaleDateString()}</p>
          <table>
            <thead>
              <tr>
                <th>ID Number</th>
                <th>Name</th>
                <th>Type of Disability</th>
                <th>Date Registered</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              ${data.map(d => `
                <tr>
                  <td>${d.id}</td>
                  <td>${d.name}</td>
                  <td>${d.disability}</td>
                  <td>${d.date}</td>
                  <td>${d.status}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
          <div class="footer">Total Records: ${data.length}</div>
          <script>window.print();<\/script>
        </body>
        </html>
      `);
      printWindow.document.close();
    }