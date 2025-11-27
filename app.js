document.addEventListener("DOMContentLoaded", () => {

  /* FONCTION AJAX NATIVE POUR RAFRAÎCHIR LES DONNÉES */
  function fetchData(action, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `api.php?action=${action}`, true);
    
    xhr.onload = function() {
      if (xhr.status === 200) {
        try {
          const response = JSON.parse(xhr.responseText);
          if (response.success) {
            callback(response.data);
          } else {
            console.error('Erreur API:', response.error);
          }
        } catch (e) {
          console.error('Erreur parsing JSON:', e);
        }
      } else {
        console.error('Erreur HTTP:', xhr.status);
      }
    };
    
    xhr.onerror = function() {
      console.error('Erreur réseau');
    };
    
    xhr.send();
  }

  /* KPI DYNAMIQUE - MISE À JOUR VIA AJAX */
  const kpiUsers = document.getElementById("kpiUsers");
  const kpiRevenue = document.getElementById("kpiRevenue");
  const kpiOrders = document.getElementById("kpiOrders");
  const kpiResponse = document.getElementById("kpiResponse");

  function updateKPIs() {
    fetchData('kpis', (data) => {
      kpiUsers.textContent = parseInt(data.users).toLocaleString();
      kpiRevenue.textContent = "€" + parseFloat(data.revenue).toLocaleString();
      kpiOrders.textContent = parseInt(data.orders).toLocaleString();
      kpiResponse.textContent = data.response + "s";
    });
  }
  
  // Rafraîchir les KPIs toutes les 30 secondes
  setInterval(updateKPIs, 30000);

  /* MISE À JOUR DES ACTIVITÉS RÉCENTES */
  function updateActivities() {
    fetchData('activities', (data) => {
      const activityList = document.getElementById('activityList');
      activityList.innerHTML = '';
      
      data.forEach(activity => {
        const li = document.createElement('li');
        li.innerHTML = `<strong>${activity.text}</strong><span class="muted">${activity.time}</span>`;
        activityList.appendChild(li);
      });
    });
  }
  
  // Rafraîchir les activités toutes les 15 secondes
  setInterval(updateActivities, 15000);

  /*REVENUE CHART */
  const revenueChart = new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
      labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
      datasets: [
        { 
          label: 'Revenue', 
          data: [12000,15000,14000,17000,18000,20000,21000,19000,23000,25000,24000,28000], 
          borderWidth:2, 
          tension:0.35, 
          fill:true, 
          borderColor:'rgba(88, 64, 255, 1)', 
          backgroundColor:'rgba(88, 64, 255, 0.08)', 
          pointRadius:0 
        },
        { 
          label: 'Profit', 
          data: [4000,5200,4800,6000,6200,7000,7200,6800,7600,8200,8000,9000], 
          borderWidth:2, 
          tension:0.35, 
          fill:true, 
          borderColor:'rgba(16, 185, 129, 1)', 
          backgroundColor:'rgba(16, 185, 129, 0.06)', 
          pointRadius:0 
        }
      ]
    },
    options: { 
      responsive:true, 
      maintainAspectRatio:false, 
      plugins:{legend:{display:false}}, 
      scales:{y:{ticks:{callback:v=>'€'+v/1000+'k'}}} 
    }
  });

  const revenueButtons = document.querySelectorAll('.revenue-card .chip');
  const revenueDataSets = {
    "7D": {
      labels: ['Day 24','Day 25','Day 26','Day 27','Day 28','Day 29','Day 30'],
      revenue: [15000, 16000, 14000, 17000, 18000, 20000, 19000],
      profit: [4000, 4200, 3800, 4500, 4700, 5200, 5000]
    },
    "30D": {
      labels: Array.from({length:30}, (_,i)=>`Day ${i+1}`),
      revenue: Array.from({length:30}, ()=>Math.floor(Math.random()*10000 + 15000)),
      profit: Array.from({length:30}, ()=>Math.floor(Math.random()*4000 + 4000))
    },
    "1Y": {
      labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
      revenue: [120000,150000,140000,170000,180000,200000,210000,190000,230000,250000,240000,280000],
      profit: [40000,52000,48000,60000,62000,70000,72000,68000,76000,82000,80000,90000]
    }
  };

  revenueButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      revenueButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const period = btn.textContent.trim();
      const dataSet = revenueDataSets[period];
      revenueChart.data.labels = dataSet.labels;
      revenueChart.data.datasets[0].data = dataSet.revenue;
      revenueChart.data.datasets[1].data = dataSet.profit;
      revenueChart.update();
    });
  });

  /*USER GROWTH CHART */
  const usersChart = new Chart(document.getElementById('usersChart'), {
    type: 'bar',
    data: { 
      labels:['Day 24','Day 25','Day 26','Day 27','Day 28','Day 29','Day 30'], 
      datasets:[{
        label:'Users', 
        data:[45,78,60,55,70,98,120], 
        borderRadius:6
      }]
    },
    options: { 
      responsive:true, 
      maintainAspectRatio:false, 
      plugins:{legend:{display:false}}, 
      scales:{y:{beginAtZero:true}}
    }
  });

  /*ORDERS DONUT CHART */
  const ordersDonut = new Chart(document.getElementById('ordersDonut'), {
    type:'doughnut',
    data:{
      labels:['Completed','Processing','Pending','Cancelled'], 
      datasets:[{
        data:[58,20,12,10], 
        backgroundColor:['#10B981','#6366F1','#FACC15','#EF4444']
      }]
    },
    options:{
      responsive:true, 
      maintainAspectRatio:false, 
      plugins:{legend:{position:'bottom'}}
    }
  });

  /* MISE À JOUR DU DONUT DEPUIS LA BASE DE DONNÉES */
  function updateOrderStatsChart() {
    fetchData('order_stats', (data) => {
      ordersDonut.data.datasets[0].data = [
        data.completed || 0,
        data.processing || 0,
        data.pending || 0,
        data.cancelled || 0
      ];
      ordersDonut.update();
    });
  }
  
  // Rafraîchir les stats toutes les 20 secondes
  setInterval(updateOrderStatsChart, 20000);

  /*FILTRAGE TABLEAU RECENT ORDERS */
  const statusFilter = document.getElementById("statusFilter");
  const tableBody = document.getElementById("ordersTableBody");

  function filterOrders() {
    const selectedStatus = statusFilter.value;
    const rows = tableBody.querySelectorAll("tr");
    
    rows.forEach(row => {
      const status = row.getAttribute('data-status');
      if (selectedStatus === "all" || status === selectedStatus) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });
    
    // Mettre à jour le graphique en fonction du filtre
    updateFilteredDonut(selectedStatus);
  }

  function updateFilteredDonut(status) {
    const rows = tableBody.querySelectorAll("tr");
    const statusCount = { completed:0, pending:0, cancelled:0, processing:0 };

    rows.forEach(row => {
      if (row.style.display !== "none") {
        const rowStatus = row.getAttribute('data-status');
        if (statusCount[rowStatus] !== undefined) {
          statusCount[rowStatus]++;
        }
      }
    });

    if (status === "all") {
      ordersDonut.data.datasets[0].data = [
        statusCount.completed,
        statusCount.processing,
        statusCount.pending,
        statusCount.cancelled
      ];
    } else {
      ordersDonut.data.datasets[0].data = [
        status === 'completed' ? statusCount.completed : 0,
        status === 'processing' ? statusCount.processing : 0,
        status === 'pending' ? statusCount.pending : 0,
        status === 'cancelled' ? statusCount.cancelled : 0
      ];
    }
    ordersDonut.update();
  }

  statusFilter.addEventListener("change", filterOrders);
  filterOrders();

  /* RAFRAÎCHIR LES COMMANDES VIA AJAX */
  function refreshOrders() {
    const currentFilter = statusFilter.value;
    fetchData(`orders&status=${currentFilter}`, (data) => {
      tableBody.innerHTML = '';
      
      data.forEach(order => {
        const tr = document.createElement('tr');
        tr.setAttribute('data-status', order.status);
        tr.innerHTML = `
          <td>${order.order_number}</td>
          <td>${order.customer_name}</td>
          <td>€${parseFloat(order.amount).toFixed(2)}</td>
          <td class="status ${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</td>
          <td>${new Date(order.order_date).toLocaleDateString('fr-FR')}</td>
        `;
        tableBody.appendChild(tr);
      });
      
      filterOrders();
    });
  }
  
  // Rafraîchir les commandes toutes les 25 secondes
  setInterval(refreshOrders, 25000);

});