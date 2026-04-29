<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Pedidos - PadColor</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 0;
            min-height: calc(100vh - 200px);
        }
        
        .products-section {
            padding: 30px;
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }
        
        .cart-section {
            background: #f8f9fa;
            border-left: 3px solid #667eea;
            padding: 30px;
            overflow-y: auto;
            max-height: calc(100vh - 200px);
            position: sticky;
            top: 0;
        }
        
        .search-filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .search-box {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .search-box:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .filter-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }
        
        .filter-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .page-filter {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #e0e0e0;
        }
        
        .page-filter label {
            font-weight: 600;
            color: #555;
            white-space: nowrap;
        }
        
        .page-input {
            flex: 1;
            max-width: 120px;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .page-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .page-btn {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .page-btn:hover {
            background: #5568d3;
        }
        
        .clear-page-btn {
            padding: 10px 15px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .clear-page-btn:hover {
            background: #c82333;
        }
        
        .active-page-filter {
            background: #fff3cd;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 2px solid #ffc107;
        }
        
        .active-page-filter span {
            font-weight: 600;
            color: #856404;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        
        .product-code {
            font-family: 'Courier New', monospace;
            color: #667eea;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 8px;
        }
        
        .product-desc {
            font-size: 14px;
            color: #333;
            margin-bottom: 12px;
            line-height: 1.4;
            min-height: 40px;
        }
        
        .product-meta {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-linea {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-categoria {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .badge-pagina {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .add-section {
            display: flex;
            gap: 10px;
        }
        
        .qty-input {
            flex: 1;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .add-btn {
            flex: 1;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .add-btn:hover {
            background: #5568d3;
            transform: scale(1.05);
        }
        
        .add-btn:active {
            transform: scale(0.98);
        }
        
        .cart-header {
            margin-bottom: 25px;
        }
        
        .cart-header h2 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .client-form {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .cart-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .stat-row:last-child {
            margin-bottom: 0;
            padding-top: 10px;
            border-top: 2px solid rgba(255,255,255,0.3);
            font-weight: bold;
            font-size: 18px;
        }
        
        .cart-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .cart-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }
        
        .cart-item-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #667eea;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .cart-item-desc {
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .cart-item-qty {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .qty-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .qty-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: #667eea;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .qty-btn:hover {
            background: #5568d3;
        }
        
        .qty-display {
            font-weight: bold;
            min-width: 40px;
            text-align: center;
        }
        
        .remove-btn {
            color: #dc3545;
            cursor: pointer;
            font-size: 12px;
            text-decoration: underline;
        }
        
        .remove-btn:hover {
            color: #c82333;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .btn {
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #28a745;
            color: white;
        }
        
        .btn-primary:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-secondary {
            background: white;
            color: #dc3545;
            border: 2px solid #dc3545;
        }
        
        .btn-secondary:hover {
            background: #dc3545;
            color: white;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .empty-cart-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .no-results-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .cart-section {
                border-left: none;
                border-top: 3px solid #667eea;
                position: relative;
                max-height: none;
            }
        }
        
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 1.8em;
            }
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛍️ Sistema de Pedidos PadColor v2.0</h1>
            <p>Catálogo Digital - Toda Ocasión 2026</p>
        </div>
        
        <div class="main-content">
            <div class="products-section">
                <div class="search-filters">
                    <input 
                        type="text" 
                        class="search-box" 
                        id="searchBox" 
                        placeholder="🔍 Buscar por código, descripción o categoría..."
                    >
                    <div class="filter-buttons" id="filterButtons"></div>
                    
                    <div class="page-filter">
                        <label>📄 Página del catálogo:</label>
                        <input 
                            type="number" 
                            class="page-input" 
                            id="pageInput" 
                            placeholder="Ej: 15"
                            min="1"
                            max="108"
                        >
                        <button class="page-btn" id="pageFilterBtn">Filtrar</button>
                    </div>
                    
                    <div id="activePageFilter" style="display: none;"></div>
                </div>
                
                <div class="products-grid" id="productsGrid">
                    <div class="loading">⏳ Cargando productos...</div>
                </div>
            </div>
            
            <div class="cart-section">
                <div class="cart-header">
                    <h2>🛒 Mi Pedido</h2>
                    
                    <div class="client-form">
                        <div class="form-group">
                            <label>Número SIMAN del Cliente *</label>
                            <input 
                                type="text" 
                                id="clientCode" 
                                placeholder="Ej: 12345"
                                maxlength="10"
                            >
                        </div>
                        <div class="form-group">
                            <label>Nombre del Cliente (opcional)</label>
                            <input 
                                type="text" 
                                id="clientName" 
                                placeholder="Ej: Tienda Centro"
                            >
                        </div>
                        <div class="form-group">
                            <label>Fecha del Pedido</label>
                            <input 
                                type="date" 
                                id="orderDate"
                            >
                        </div>
                    </div>
                </div>
                
                <div class="cart-stats" id="cartStats">
                    <div class="stat-row">
                        <span>Líneas de productos:</span>
                        <span id="totalLines">0</span>
                    </div>
                    <div class="stat-row">
                        <span>Cantidad total:</span>
                        <span id="totalQty">0</span>
                    </div>
                </div>
                
                <div class="cart-items" id="cartItems">
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <p>Tu pedido está vacío</p>
                        <p style="font-size: 12px; margin-top: 10px;">Agrega productos desde el catálogo</p>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button class="btn btn-primary" id="generateBtn" disabled>
                        📥 Generar Pedido (Excel)
                    </button>
                    <button class="btn btn-secondary" id="clearBtn" disabled>
                        🗑️ Limpiar Pedido
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Base de datos de productos
        const PRODUCTOS_DB = [
  {
    "codigo": "682158033699",
    "descripcion": "ETIQUETA ESCOLAR FRESITAS PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 2
  },
  {
    "codigo": "682158033705",
    "descripcion": "ETIQUETA ESCOLAR CARIÑITOS PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 2
  },
  {
    "codigo": "682158033712",
    "descripcion": "ETIQUETA ESCOLAR PRINCESITAS PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 2
  },
  {
    "codigo": "682158033729",
    "descripcion": "ETIQUETA ESCOLAR UNICORNIO PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 2
  },
  {
    "codigo": "682158033736",
    "descripcion": "ETIQUETA ESCOLAR CAPIBARA PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 2
  },
  {
    "codigo": "682158033743",
    "descripcion": "ETIQUETA ESCOLAR CUPCAKES PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 2
  },
  {
    "codigo": "682158033750",
    "descripcion": "ETIQUETA ESCOLAR CARRITOS PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 2
  },
  {
    "codigo": "682158033767",
    "descripcion": "ETIQUETA ESCOLAR DINOS PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 2
  },
  {
    "codigo": "682158033774",
    "descripcion": "ETIQUETA ESCOLAR AUTOS PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 3
  },
  {
    "codigo": "682158033781",
    "descripcion": "ETIQUETA ESCOLAR FUTBOL PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 3
  },
  {
    "codigo": "682158033798",
    "descripcion": "ETIQUETA ESCOLAR CITY PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 3
  },
  {
    "codigo": "682158033804",
    "descripcion": "ETIQUETA ESCOLAR SCHOOL PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 3
  },
  {
    "codigo": "682158033811",
    "descripcion": "ETIQUETA ESCOLAR LISO 01 PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 3
  },
  {
    "codigo": "682158033828",
    "descripcion": "ETIQUETA ESCOLAR LISO 02 PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 3
  },
  {
    "codigo": "682158033835",
    "descripcion": "ETIQUETA ESCOLAR LISO 03 PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 3
  },
  {
    "codigo": "682158033842",
    "descripcion": "ETIQUETA ESCOLAR LISO 04 PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 3
  },
  {
    "codigo": "682158023607",
    "descripcion": "0374 TRACKS BOLSA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 4
  },
  {
    "codigo": "682158023614",
    "descripcion": "0375 TRACKS BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 4
  },
  {
    "codigo": "682158027216",
    "descripcion": "1846 TRACKS BOLSA EXTRA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 4
  },
  {
    "codigo": "682158033026",
    "descripcion": "0374 HADITAS BOLSA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 4
  },
  {
    "codigo": "682158033033",
    "descripcion": "0375 HADITAS BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 4
  },
  {
    "codigo": "682158033040",
    "descripcion": "0480 HADITAS BOLSA CHICA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 4
  },
  {
    "codigo": "682158033859",
    "descripcion": "ETIQUETA ESCOLAR LISO 05 PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 4
  },
  {
    "codigo": "682158033866",
    "descripcion": "ETIQUETA ESCOLAR LISO 06 PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 4
  },
  {
    "codigo": "682158033873",
    "descripcion": "ETIQUETA ESCOLAR LISO 07 PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 4
  },
  {
    "codigo": "682158033880",
    "descripcion": "ETIQUETA ESCOLAR LISO 08 PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 4
  },
  {
    "codigo": "682158033897",
    "descripcion": "ETIQUETA ESCOLAR LISO 09 PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 4
  },
  {
    "codigo": "682158033903",
    "descripcion": "ETIQUETA ESCOLAR LISO ROSA PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 4
  },
  {
    "codigo": "682158033910",
    "descripcion": "ETIQUETA ESCOLAR LISO AZUL PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 4
  },
  {
    "codigo": "682158033927",
    "descripcion": "ETIQUETA ESCOLAR LISO ROJO PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 4
  },
  {
    "codigo": "682158033934",
    "descripcion": "ETIQUETA ESCOLAR LISO AZUL REY PAQ CON 8 PLANILLAS",
    "linea": "Escolar",
    "categoria": "Etiqueta",
    "en_catalogo": "Sí",
    "catalogo": "Escolar",
    "pagina": 4
  },
  {
    "codigo": "682158025632",
    "descripcion": "0375 FABULAS BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 5
  },
  {
    "codigo": "682158025649",
    "descripcion": "0480 FABULAS BOLSA CHICA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 5
  },
  {
    "codigo": "682158025960",
    "descripcion": "0374 DISFRACES BOLSA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 5
  },
  {
    "codigo": "682158025977",
    "descripcion": "0375 DISFRACES BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 5
  },
  {
    "codigo": "682158025984",
    "descripcion": "0480 DISFRACES BOLSA CHICA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 5
  },
  {
    "codigo": "682158031176",
    "descripcion": "0374 TIERNA PRINCESA BOLSA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 5
  },
  {
    "codigo": "682158031183",
    "descripcion": "0375 TIERNA PRINCESA BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 5
  },
  {
    "codigo": "682158031190",
    "descripcion": "0480 TIERNA PRINCESA BOLSA CHICA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 5
  },
  {
    "codigo": "682158023188",
    "descripcion": "0374 FRESITAS BOLSA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 6
  },
  {
    "codigo": "682158023195",
    "descripcion": "0375 FRESITAS BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 6
  },
  {
    "codigo": "682158032838",
    "descripcion": "0374 CITY BOLSA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 6
  },
  {
    "codigo": "682158032845",
    "descripcion": "0375 CITY BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 6
  },
  {
    "codigo": "682158032852",
    "descripcion": "0480 CITY BOLSA CHICA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 6
  },
  {
    "codigo": "682158033002",
    "descripcion": "1846 CITY BOLSA EXTRA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 6
  },
  {
    "codigo": "682158026042",
    "descripcion": "1846 PRINCESITAS BOLSA EXTRA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 7
  },
  {
    "codigo": "682158031046",
    "descripcion": "0374 UNICORNIO MAGICO BOLSA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 7
  },
  {
    "codigo": "682158031053",
    "descripcion": "0375 UNICORNIO MAGICO BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 7
  },
  {
    "codigo": "682158031060",
    "descripcion": "0480 UNICORNIO MAGICO BOLSA CHICA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 7
  },
  {
    "codigo": "682158031077",
    "descripcion": "0374 ROBOTRONIX BOLSA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 7
  },
  {
    "codigo": "682158031084",
    "descripcion": "0375 ROBOTRONIX BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 7
  },
  {
    "codigo": "682158031091",
    "descripcion": "0480 ROBOTRONIX BOLSA CHICA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 7
  },
  {
    "codigo": "682158025120",
    "descripcion": "0374 INTREPIDOS BOLSA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 8
  },
  {
    "codigo": "682158025137",
    "descripcion": "0375 INTREPIDOS BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 8
  },
  {
    "codigo": "682158025144",
    "descripcion": "0480 INTREPIDOS BOLSA CHICA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 8
  },
  {
    "codigo": "682158032807",
    "descripcion": "0374 POWER BOLSA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 8
  },
  {
    "codigo": "682158032814",
    "descripcion": "0375 POWER BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 8
  },
  {
    "codigo": "682158032821",
    "descripcion": "0480 POWER BOLSA CHICA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 8
  },
  {
    "codigo": "682158020712",
    "descripcion": "0374 FIESTA LOCA BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 9
  },
  {
    "codigo": "682158020729",
    "descripcion": "0375 FIESTA LOCA BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 9
  },
  {
    "codigo": "682158020736",
    "descripcion": "0480 FIESTA LOCA BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 9
  },
  {
    "codigo": "682158025069",
    "descripcion": "0374 MODAPAD BOLSA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 9
  },
  {
    "codigo": "682158025076",
    "descripcion": "0375 MODAPAD BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 9
  },
  {
    "codigo": "682158025083",
    "descripcion": "0480 MODAPAD BOLSA CHICA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 9
  },
  {
    "codigo": "682158026028",
    "descripcion": "1846 FIESTA LOCA BOLSA EXTRA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 9
  },
  {
    "codigo": "682158027544",
    "descripcion": "0374 MUNDO FELIZ BOLSA GRANDE INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 9
  },
  {
    "codigo": "682158027551",
    "descripcion": "0375 MUNDO FELIZ BOLSA MEDIANA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 9
  },
  {
    "codigo": "682158027568",
    "descripcion": "0480 MUNDO FELIZ BOLSA CHICA INFANTIL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 9
  },
  {
    "codigo": "682158024130",
    "descripcion": "0480 TECNOFIESTA BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 10
  },
  {
    "codigo": "682158025861",
    "descripcion": "0374 DULCE CUMPLE BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 10
  },
  {
    "codigo": "682158025878",
    "descripcion": "0375 DULCE CUMPLE BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 10
  },
  {
    "codigo": "682158025885",
    "descripcion": "0480 DULCE CUMPLE BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 10
  },
  {
    "codigo": "682158031299",
    "descripcion": "1846 FIESTA DE GLOBOS BOLSA EXTRA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 10
  },
  {
    "codigo": "682158031305",
    "descripcion": "0374 FIESTA DE GLOBOS BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 10
  },
  {
    "codigo": "682158031312",
    "descripcion": "0375 FIESTA DE GLOBOS BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 10
  },
  {
    "codigo": "682158031329",
    "descripcion": "0480 FIESTA DE GLOBOS BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 10
  },
  {
    "codigo": "682158027254",
    "descripcion": "1846 CARNAVAL BOLSA EXTRA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 11
  },
  {
    "codigo": "682158027360",
    "descripcion": "0374 CARNAVAL BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 11
  },
  {
    "codigo": "682158027377",
    "descripcion": "0375 CARNAVAL BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 11
  },
  {
    "codigo": "682158027384",
    "descripcion": "0480 CARNAVAL BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 11
  },
  {
    "codigo": "682158030988",
    "descripcion": "0374 SUPER REGALO BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 11
  },
  {
    "codigo": "682158030995",
    "descripcion": "0375 SUPER REGALO BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 11
  },
  {
    "codigo": "682158031008",
    "descripcion": "0480 SUPER REGALO BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 11
  },
  {
    "codigo": "682158031794",
    "descripcion": "0374 FIESTA ALEGRE KRAFT BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 11
  },
  {
    "codigo": "682158031800",
    "descripcion": "0375 FIESTA ALEGRE KRAFT BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 11
  },
  {
    "codigo": "682158031817",
    "descripcion": "0480 FIESTA ALEGRE KRAFT BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 11
  },
  {
    "codigo": "682158027230",
    "descripcion": "1846 COLORMANIA BOLSA EXTRA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 12
  },
  {
    "codigo": "682158027391",
    "descripcion": "0374 COLORMANIA BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 12
  },
  {
    "codigo": "682158027407",
    "descripcion": "0375 COLORMANIA BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 12
  },
  {
    "codigo": "682158027414",
    "descripcion": "0480 COLORMANIA BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 12
  },
  {
    "codigo": "682158032890",
    "descripcion": "0374 CUMPLE GENIAL BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 12
  },
  {
    "codigo": "682158032906",
    "descripcion": "0375 CUMPLE GENIAL BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 12
  },
  {
    "codigo": "682158032913",
    "descripcion": "0480 CUMPLE GENIAL BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 12
  },
  {
    "codigo": "682158024086",
    "descripcion": "0374 PELUDOS BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 13
  },
  {
    "codigo": "682158024093",
    "descripcion": "0375 PELUDOS BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 13
  },
  {
    "codigo": "682158028978",
    "descripcion": "0374 INSPIRACIÓN BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 13
  },
  {
    "codigo": "682158028985",
    "descripcion": "0375 INSPIRACIÓN BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 13
  },
  {
    "codigo": "682158028992",
    "descripcion": "0480 INSPIRACIÓN BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 13
  },
  {
    "codigo": "682158031015",
    "descripcion": "0374 FIESTA JOVEN BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 13
  },
  {
    "codigo": "682158031022",
    "descripcion": "0375 FIESTA JOVEN BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 13
  },
  {
    "codigo": "682158031039",
    "descripcion": "0480 FIESTA JOVEN BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 13
  },
  {
    "codigo": "682158025298",
    "descripcion": "0374 VELITAS BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 14
  },
  {
    "codigo": "682158025310",
    "descripcion": "0480 VELITAS BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 14
  },
  {
    "codigo": "682158032920",
    "descripcion": "0374 REGALO ESTRELLA BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 14
  },
  {
    "codigo": "682158032937",
    "descripcion": "0375 REGALO ESTRELLA BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 14
  },
  {
    "codigo": "682158032944",
    "descripcion": "0480 REGALO ESTRELLA BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 14
  },
  {
    "codigo": "682158032999",
    "descripcion": "1846 REGALO ESTRELLA BOLSA EXTRA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 14
  },
  {
    "codigo": "682158025939",
    "descripcion": "0374 FESTIVAL BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 15
  },
  {
    "codigo": "682158025946",
    "descripcion": "0375 FESTIVAL BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 15
  },
  {
    "codigo": "682158025953",
    "descripcion": "0480 FESTIVAL BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 15
  },
  {
    "codigo": "682158027629",
    "descripcion": "0374 LASER BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 15
  },
  {
    "codigo": "682158027636",
    "descripcion": "0375 LASER BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 15
  },
  {
    "codigo": "682158027643",
    "descripcion": "0480 LASER BOLSA CHICA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 15
  },
  {
    "codigo": "682158028831",
    "descripcion": "1846 COLOR PARTY BOLSA EXTRA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 15
  },
  {
    "codigo": "682158028848",
    "descripcion": "0374 COLOR PARTY BOLSA GRANDE CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 15
  },
  {
    "codigo": "682158028855",
    "descripcion": "0375 COLOR PARTY BOLSA MEDIANA CUMPLEAÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 15
  },
  {
    "codigo": "682158020644",
    "descripcion": "0374 SORPRESA BOLSA GRANDE TEXTURA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 16
  },
  {
    "codigo": "682158020651",
    "descripcion": "0375 SORPRESA BOLSA MEDIANA TEXTURA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 16
  },
  {
    "codigo": "682158027452",
    "descripcion": "0374 PRISMA BOLSA GRANDE TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 16
  },
  {
    "codigo": "682158027469",
    "descripcion": "0375 PRISMA BOLSA MEDIANA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 16
  },
  {
    "codigo": "682158027476",
    "descripcion": "0480 PRISMA BOLSA CHICA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 16
  },
  {
    "codigo": "682158029005",
    "descripcion": "0374 REHILETE BOLSA GRANDE TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 16
  },
  {
    "codigo": "682158029012",
    "descripcion": "0375 REHILETE BOLSA MEDIANA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 16
  },
  {
    "codigo": "682158029029",
    "descripcion": "0480 REHILETE BOLSA CHICA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 16
  },
  {
    "codigo": "682158027278",
    "descripcion": "0374 SILVESTRE BOLSA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 17
  },
  {
    "codigo": "682158027285",
    "descripcion": "0375 SILVESTRE BOLSA MEDIANA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 17
  },
  {
    "codigo": "682158027292",
    "descripcion": "0480 SILVESTRE BOLSA CHICA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 17
  },
  {
    "codigo": "682158033149",
    "descripcion": "0374 CANDY BOLSA GRANDE TEXTURA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 17
  },
  {
    "codigo": "682158033156",
    "descripcion": "0375 CANDY BOLSA MEDIANA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 17
  },
  {
    "codigo": "682158033163",
    "descripcion": "0480 CANDY BOLSA CHICA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 17
  },
  {
    "codigo": "682158020743",
    "descripcion": "0374 FLORES ACUARELA BOLSA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 18
  },
  {
    "codigo": "682158033071",
    "descripcion": "0374 INFINITY BOLSA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 18
  },
  {
    "codigo": "682158033088",
    "descripcion": "0375 INFINITY BOLSA MEDIANA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 18
  },
  {
    "codigo": "682158025830",
    "descripcion": "0374 JARDIN BOLSA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 19
  },
  {
    "codigo": "682158025847",
    "descripcion": "0375 JARDIN BOLSA MEDIANA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 19
  },
  {
    "codigo": "682158025854",
    "descripcion": "0480 JARDIN BOLSA CHICA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 19
  },
  {
    "codigo": "682158027223",
    "descripcion": "1846 JARDIN BOLSA EXTRA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 19
  },
  {
    "codigo": "682158032968",
    "descripcion": "0375 MARIPOSAS BOLSA MEDIANA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 19
  },
  {
    "codigo": "682158032975",
    "descripcion": "0480 MARIPOSAS BOLSA CHICA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 19
  },
  {
    "codigo": "682158019884",
    "descripcion": "1846 POESIA FLORAL BOLSA EXTRA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 20
  },
  {
    "codigo": "682158019914",
    "descripcion": "0480 POESIA FLORAL BOLSA CHICA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 20
  },
  {
    "codigo": "682158033262",
    "descripcion": "0375 AQUAFLORES BOLSA MEDIANA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 20
  },
  {
    "codigo": "682158033279",
    "descripcion": "0480 AQUA FLORES BOLSA CHICA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 20
  },
  {
    "codigo": "682158029036",
    "descripcion": "0374 SOÑADORA BOLSA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 21
  },
  {
    "codigo": "682158029043",
    "descripcion": "0375 SOÑADORA BOLSA MEDIANA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 21
  },
  {
    "codigo": "682158029050",
    "descripcion": "0480 SOÑADORA BOLSA CHICA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 21
  },
  {
    "codigo": "682158032777",
    "descripcion": "0374 SUBLIME BOLSA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 21
  },
  {
    "codigo": "682158032784",
    "descripcion": "0375 SUBLIME BOLSA MEDIANA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 21
  },
  {
    "codigo": "682158032791",
    "descripcion": "0480 SUBLIME BOLSA CHICA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 21
  },
  {
    "codigo": "682158027315",
    "descripcion": "0375 ILUSION FLORAL BOLSA MEDIANA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 22
  },
  {
    "codigo": "682158027322",
    "descripcion": "0480 ILUSION FLORAL BOLSA CHICA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 22
  },
  {
    "codigo": "682158027339",
    "descripcion": "0374 AMANECER BOLSA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 22
  },
  {
    "codigo": "682158027346",
    "descripcion": "0375 AMANECER BOLSA MEDIANA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 22
  },
  {
    "codigo": "682158028947",
    "descripcion": "0374 LADY BOLSA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 22
  },
  {
    "codigo": "682158028954",
    "descripcion": "0375 LADY BOLSA MEDIANA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 22
  },
  {
    "codigo": "682158028961",
    "descripcion": "0480 LADY BOLSA CHICA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 22
  },
  {
    "codigo": "682158020057",
    "descripcion": "0374 BEBE ARCOIRIS BOLSA GRANDE BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 23
  },
  {
    "codigo": "682158020064",
    "descripcion": "0375 BEBE ARCOIRIS BOLSA MEDIANA BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 23
  },
  {
    "codigo": "682158030896",
    "descripcion": "0374 FLORALIA BOLSA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 23
  },
  {
    "codigo": "682158030902",
    "descripcion": "0375 FLORALIA BOLSA MEDIANA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 23
  },
  {
    "codigo": "682158030919",
    "descripcion": "0480 FLORALIA BOLSA CHICA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 23
  },
  {
    "codigo": "682158031145",
    "descripcion": "0374 COQUETA BOLSA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 23
  },
  {
    "codigo": "682158031169",
    "descripcion": "0480 COQUETA BOLSA CHICA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 23
  },
  {
    "codigo": "682158027513",
    "descripcion": "0374 PICCOLO BOLSA GRANDE BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 24
  },
  {
    "codigo": "682158027520",
    "descripcion": "0375 PICCOLO BOLSA MEDIANA BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 24
  },
  {
    "codigo": "682158027841",
    "descripcion": "1846 PICCOLO BOLSA EXTRA GRANDE BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 24
  },
  {
    "codigo": "682158033231",
    "descripcion": "0374 BABY CROCHET BOLSA GRANDE BEBES",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 24
  },
  {
    "codigo": "682158033248",
    "descripcion": "0375 BABY CROCHET BOLSA MEDIANA BEBES",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 24
  },
  {
    "codigo": "682158027247",
    "descripcion": "1846 BABY CLUB BOLSA EXTRA GRANDE BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 25
  },
  {
    "codigo": "682158027421",
    "descripcion": "0374 BABY CLUB BOLSA GRANDE BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 25
  },
  {
    "codigo": "682158027438",
    "descripcion": "0375 BABY CLUB BOLSA MEDIANA BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 25
  },
  {
    "codigo": "682158033057",
    "descripcion": "0374 BABY STAR BOLSA GRANDE BEBES",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 25
  },
  {
    "codigo": "682158033064",
    "descripcion": "0375 BABY STAR BOLSA MEDIANA BEBES",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 25
  },
  {
    "codigo": "682158020682",
    "descripcion": "0374 PIN PON BOLSA GRANDE BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 26
  },
  {
    "codigo": "682158020699",
    "descripcion": "0375 PIN PON BOLSA MEDIANA BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 26
  },
  {
    "codigo": "682158031107",
    "descripcion": "0374 BABY LANDIA BOLSA GRANDE BEBES",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 26
  },
  {
    "codigo": "682158031111",
    "descripcion": "0375 BABY LANDIA BOLSA MEDIANA BEBES",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 26
  },
  {
    "codigo": "682158031122",
    "descripcion": "0480 BABY LANDIA BOLSA CHICA BEBES",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 26
  },
  {
    "codigo": "682158031206",
    "descripcion": "0374 BEBES LINDOS BOLSA GRANDE BEBÉ",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 26
  },
  {
    "codigo": "682158031213",
    "descripcion": "0375 BEBES LINDOS BOLSA MEDIANA BEBÉ",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 26
  },
  {
    "codigo": "682158031220",
    "descripcion": "0480 BEBES LINDOS BOLSA CHICA BEBÉ",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 26
  },
  {
    "codigo": "682158023485",
    "descripcion": "0374 ARRULLO BOLSA GRANDE BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 27
  },
  {
    "codigo": "682158023492",
    "descripcion": "0375 ARRULLO BOLSA MEDIANA BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 27
  },
  {
    "codigo": "682158023508",
    "descripcion": "0480 ARRULLO BOLSA CHICA BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 27
  },
  {
    "codigo": "682158033101",
    "descripcion": "0374 BABY ZOO BOLSA GRANDE BEBES",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 27
  },
  {
    "codigo": "682158033118",
    "descripcion": "0375 BABY ZOO BOLSA MEDIANA BEBES",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 27
  },
  {
    "codigo": "682158020026",
    "descripcion": "0374 PAN DE VIDA BOLSA GRANDE 1RA COMUNION",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 28
  },
  {
    "codigo": "682158020033",
    "descripcion": "0375 PAN DE VIDA BOLSA MEDIANA 1RA COMUNION",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 28
  },
  {
    "codigo": "682158020040",
    "descripcion": "0480 PAN DE VIDA BOLSA CHICA 1RA COMUNION",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 28
  },
  {
    "codigo": "682158028923",
    "descripcion": "0374 BLANCA COMUNIÓN BOLSA GRANDE 1RA COMUNIÓN",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 28
  },
  {
    "codigo": "682158028930",
    "descripcion": "0375 BLANCA COMUNIÓN BOLSA MEDIANA 1RA COMUNIÓN",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 28
  },
  {
    "codigo": "682158029173",
    "descripcion": "0374 SONRISITAS BOLSA GRANDE BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 28
  },
  {
    "codigo": "682158029180",
    "descripcion": "0375 SONRISITAS BOLSA MEDIANA BEBE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 28
  },
  {
    "codigo": "682158023065",
    "descripcion": "0374 HIJOS DE DIOS BOLSA GRANDE BAUTIZO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 29
  },
  {
    "codigo": "682158023072",
    "descripcion": "0375 HIJOS DE DIOS BOLSA MEDIANA BAUTIZO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 29
  },
  {
    "codigo": "682158029142",
    "descripcion": "0374 ALELUYA BOLSA GRANDE BAUTIZO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 29
  },
  {
    "codigo": "682158029159",
    "descripcion": "0375 ALELUYA BOLSA MEDIANA BAUTIZAO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 29
  },
  {
    "codigo": "682158029166",
    "descripcion": "0480 ALELUYA BOLSA CHICA BAUTIZO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 29
  },
  {
    "codigo": "682158029579",
    "descripcion": "0374 LUZ DEL ALMA BOLSA GRANDE BAUTIZO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 29
  },
  {
    "codigo": "682158029586",
    "descripcion": "0375 LUZ DEL ALMA BOLSA MEDIANA BAUTIZO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 29
  },
  {
    "codigo": "682158023126",
    "descripcion": "0374 XV PRIMAVERAS BOLSA GRANDE XV AÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 30
  },
  {
    "codigo": "682158023133",
    "descripcion": "0375 XV PRIMAVERAS BOLSA MEDIANA XV AÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 30
  },
  {
    "codigo": "682158023355",
    "descripcion": "1846 XV PRIMAVERAS BOLSA EXTRA GRANDE XV AÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 30
  },
  {
    "codigo": "682158029739",
    "descripcion": "0374 FIESTA MAGICA BOLSA GRANDE XV AÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 30
  },
  {
    "codigo": "682158029746",
    "descripcion": "0375 FIESTA MAGICA BOLSA MEDIANA XV AÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 30
  },
  {
    "codigo": "682158028794",
    "descripcion": "1846 XV ILUSIONES BOLSA EXTRA GRANDE XV AÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 31
  },
  {
    "codigo": "682158028800",
    "descripcion": "0374 XV ILUSIONES BOLSA GRANDE XV AÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 31
  },
  {
    "codigo": "682158028817",
    "descripcion": "0375 XV ILUSIONES BOLSA MEDIANA XV AÑOS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 31
  },
  {
    "codigo": "682158029081",
    "descripcion": "0374 ALMAS GEMELAS BOLSA GRANDE BODA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 31
  },
  {
    "codigo": "682158029098",
    "descripcion": "0375 ALMAS GEMELAS BOLSA MEDIANA BODA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 31
  },
  {
    "codigo": "682158031268",
    "descripcion": "1846 NUESTRA HISTORIA BOLSA EXTRA GRANDE BODA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 31
  },
  {
    "codigo": "682158031275",
    "descripcion": "0374 NUESTRA HISTORIA BOLSA GRANDE BODA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 31
  },
  {
    "codigo": "682158031282",
    "descripcion": "0375 NUESTRA HISTORIA BOLSA MEDIANA BODA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 31
  },
  {
    "codigo": "682158023096",
    "descripcion": "0374 UN SOLO CORAZON BOLSA GRANDE BODA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 32
  },
  {
    "codigo": "682158023102",
    "descripcion": "0375 UN SOLO CORAZON BOLSA MEDIANA BODA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 32
  },
  {
    "codigo": "682158027261",
    "descripcion": "1846 ENLACE BOLSA EXTRA GRANDE BODA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 32
  },
  {
    "codigo": "682158027483",
    "descripcion": "0375 ENLACE BOLSA MEDIANA BODA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 32
  },
  {
    "codigo": "682158027490",
    "descripcion": "0374 ENLACE BOLSA GRANDE BODA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 32
  },
  {
    "codigo": "682158029555",
    "descripcion": "0374 UN GRAN AMOR BOLSA GRANDE BODA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 32
  },
  {
    "codigo": "682158029562",
    "descripcion": "0375 UN GRAN AMOR BOLSA MEDIANA BODA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 32
  },
  {
    "codigo": "682158028671",
    "descripcion": "0375 BIRRETE BOLSA MEDIANA GRADUACION",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 33
  },
  {
    "codigo": "682158028688",
    "descripcion": "0374 BIRRETE BOLSA GRANDE GRADUACION",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 33
  },
  {
    "codigo": "682158028695",
    "descripcion": "1846 BIRRETE BOLSA EXTRA GRANDE GRADUACION",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 33
  },
  {
    "codigo": "682158033125",
    "descripcion": "0374 GANADORES BOLSA GRANDE GRADUACION",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 33
  },
  {
    "codigo": "682158033132",
    "descripcion": "0375 GANADORES BOLSA MEDIANA GRADUACION",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 33
  },
  {
    "codigo": "682158020163",
    "descripcion": "0374 GRAN LOGRO BOLSA GRANDE GRADUACION",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 34
  },
  {
    "codigo": "682158020170",
    "descripcion": "0375 GRAN LOGRO BOLSA MEDIANA GRADUACION",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 34
  },
  {
    "codigo": "682158030926",
    "descripcion": "0374 EXCELENCIA BOLSA GRANDE GRADUACION",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 34
  },
  {
    "codigo": "682158030933",
    "descripcion": "0375 EXCELENCIA BOLSA MEDIANA GRADUACION",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 34
  },
  {
    "codigo": "682158030940",
    "descripcion": "0374 GRADUAKIDS BOLSA GRANDE GRADUACIÓN",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 34
  },
  {
    "codigo": "682158030957",
    "descripcion": "0375 GRADUAKIDS BOLSA MEDIANA GRADUACIÓN",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 34
  },
  {
    "codigo": "682158025779",
    "descripcion": "0374 FORTUNA BOLSA GRANDE CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 35
  },
  {
    "codigo": "682158025786",
    "descripcion": "0375 FORTUNA BOLSA MEDIANA CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 35
  },
  {
    "codigo": "682158029616",
    "descripcion": "1846 LUXEMBURGO BOLSA EXTRA GRANDE CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 35
  },
  {
    "codigo": "682158031237",
    "descripcion": "0374 LUXEMBURGO BOLSA GRANDE CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 35
  },
  {
    "codigo": "682158031244",
    "descripcion": "0375 LUXEMBURGO BOLSA MEDIANA CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 35
  },
  {
    "codigo": "682158031251",
    "descripcion": "0480 LUXEMBURGO BOLSA CHICA CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 35
  },
  {
    "codigo": "682158025328",
    "descripcion": "0374 MONTANA BOLSA GRANDE CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 36
  },
  {
    "codigo": "682158025335",
    "descripcion": "0375 MONTANA BOLSA MEDIANA CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 36
  },
  {
    "codigo": "682158025380",
    "descripcion": "0374 DEPORTES BOLSA GRANDE CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 36
  },
  {
    "codigo": "682158025595",
    "descripcion": "0374 STYLOS BOLSA GRANDE CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 37
  },
  {
    "codigo": "682158025601",
    "descripcion": "0375 STYLOS BOLSA MEDIANA CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 37
  },
  {
    "codigo": "682158025908",
    "descripcion": "0374 NOBLE BOLSA GRANDE CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 37
  },
  {
    "codigo": "682158025915",
    "descripcion": "0375 NOBLE BOLSA MEDIANA CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 37
  },
  {
    "codigo": "682158029197",
    "descripcion": "0374 RALLY BOLSA GRANDE CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 37
  },
  {
    "codigo": "682158029203",
    "descripcion": "0375 RALLY BOLSA MEDIANA CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 37
  },
  {
    "codigo": "682158029210",
    "descripcion": "0480 RALLY BOLSA CHICA CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 37
  },
  {
    "codigo": "682158025809",
    "descripcion": "2580 ESTAMPADA TENDENCIAS BOLSA GRANDE",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 38
  },
  {
    "codigo": "682158025816",
    "descripcion": "2581 ESTAMPADA TENDENCIAS BOLSA MEDIANA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 38
  },
  {
    "codigo": "682158029227",
    "descripcion": "0374 POTRO BOLSA GRANDE CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 38
  },
  {
    "codigo": "682158029234",
    "descripcion": "0375 POTRO BOLSA MEDIANA CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 38
  },
  {
    "codigo": "682158029241",
    "descripcion": "0480 POTRO BOLSA CHICA CABALLERO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 38
  },
  {
    "codigo": "682158014056",
    "descripcion": "1405 TEXTURA METAL BOLSA MEDIANA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Mediana Metal",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 39
  },
  {
    "codigo": "682158031664",
    "descripcion": "3166 TEXTURA METAL 2 BOLSA MEDIANA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Mediana Metal",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 39
  },
  {
    "codigo": "682158020132",
    "descripcion": "0374 KRAFT COLORES LISOS 1 BOLSA GRANDE TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Grande Kraft",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 40
  },
  {
    "codigo": "682158020149",
    "descripcion": "0375 KRAFT COLORES LISOS 1 BOLSA MEDIANA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Mediana Kraft",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 40
  },
  {
    "codigo": "682158020156",
    "descripcion": "0480 KRAFT COLORES LISOS 1 BOLSA CHICA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Chica Kraft Lisa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 40
  },
  {
    "codigo": "682158024147",
    "descripcion": "0374 KRAFT COLORES LISOS 2 BOLSA GRANDE TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Grande Kraft",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 40
  },
  {
    "codigo": "682158024154",
    "descripcion": "0375 KRAFT COLORES LISOS 2 BOLSA MEDIANA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Mediana Kraft",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 40
  },
  {
    "codigo": "682158024161",
    "descripcion": "0480 KRAFT COLORES LISOS 2 BOLSA CHICA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Chica Kraft Lisa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 40
  },
  {
    "codigo": "682158027094",
    "descripcion": "1846 KRAFT COLORES LISOS 1 BOLSA EXTRA GRANDE TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Extra Grande Kraft",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 40
  },
  {
    "codigo": "682158027100",
    "descripcion": "1846 KRAFT COLORES LISOS 2 BOLSA EXTRA GRANDE TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Extra Grande Kraft",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 40
  },
  {
    "codigo": "682158027193",
    "descripcion": "0375 LINEAS COLOR BOLSA MEDIANA TEXTURAS KRAFT BLANCO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Mediana Kraft Blanco",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 41
  },
  {
    "codigo": "682158027209",
    "descripcion": "0480 LINEAS COLOR BOLSA CHICA TEXTURAS KRAFT BLANCO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Chica Kraft Blanco",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 41
  },
  {
    "codigo": "682158027872",
    "descripcion": "0374 NEON BOLSA GRANDE TEXTURAS KRAFT BLANCO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Grande Kraft Blanco",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 41
  },
  {
    "codigo": "682158027889",
    "descripcion": "0375 NEON BOLSA MEDIANA TEXTURAS KRAFT BLANCO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Mediana Kraft Blanco",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 41
  },
  {
    "codigo": "682158027896",
    "descripcion": "0480 NEON BOLSA CHICA TEXTURAS KRAFT BLANCO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Chica Kraft Blanco",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 41
  },
  {
    "codigo": "682158027902",
    "descripcion": "0374 LIGHT BOLSA GRANDE TEXTURAS KRAFT BLANCO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Grande Kraft Blanco",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 41
  },
  {
    "codigo": "682158027919",
    "descripcion": "0375 LIGHT BOLSA MEDIANA TEXTURAS KRAFT BLANCO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Mediana Kraft Blanco",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 41
  },
  {
    "codigo": "682158027926",
    "descripcion": "0480 LIGHT BOLSA CHICA TEXTURAS KRAFT BLANCO",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Chica Kraft Blanco",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 41
  },
  {
    "codigo": "682158014797",
    "descripcion": "1479 KRAFT LISA BOLSA GRANDE TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Grande Kraft Lisa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 42
  },
  {
    "codigo": "682158014803",
    "descripcion": "1480 KRAFT LISA BOLSA MEDIANA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Mediana Kraft Lisa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 42
  },
  {
    "codigo": "682158014810",
    "descripcion": "1481 KRAFT LISA BOLSA CHICA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Chica Kraft Lisa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 42
  },
  {
    "codigo": "682158014926",
    "descripcion": "1492 KRAFT LISA BOLSA BOTELLERA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Botellera Kraft Lisa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 42
  },
  {
    "codigo": "682158027131",
    "descripcion": "2713 LINEAS ORO BOLSA KRAFT GRANDE TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Grande Kraft",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 42
  },
  {
    "codigo": "682158027148",
    "descripcion": "2714 LINEAS ORO BOLSA KRAFT MEDIANA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Mediana Kraft",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 42
  },
  {
    "codigo": "682158027155",
    "descripcion": "2715 LINEAS ORO BOLSA KRAFT CHICA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Chica Kraft",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 42
  },
  {
    "codigo": "682158027667",
    "descripcion": "0374 CARRUSEL KRAFT BOLSA GRANDE TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Grande Kraft",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 42
  },
  {
    "codigo": "682158027674",
    "descripcion": "0375 CARRUSEL KRAFT BOLSA MEDIANA TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Mediana Kraft",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 42
  },
  {
    "codigo": "682158027681",
    "descripcion": "1795 CARRUSEL KRAFT BOLSA BOUTIQUE TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa boutique",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 42
  },
  {
    "codigo": "682158027865",
    "descripcion": "2786 KRAFT LISA BOLSA EXTRA GRANDE TEXTURAS",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Extra Grande Kraft",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 42
  },
  {
    "codigo": "682158020675",
    "descripcion": "0615 ROYAL BOLSA BOTELLERA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa botellera",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 43
  },
  {
    "codigo": "682158023348",
    "descripcion": "0615 HORA FELIZ BOLSA BOTELLERA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa botellera",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 43
  },
  {
    "codigo": "682158026578",
    "descripcion": "0615 BRISA BOLSA BOTELLERA",
    "linea": "Toda ocasión",
    "categoria": "Bolsa botellera",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 43
  },
  {
    "codigo": "682158031503",
    "descripcion": "3136 BOLITAS BOLSA EXTRA GRANDE ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Extra Grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 44
  },
  {
    "codigo": "682158032180",
    "descripcion": "3136 FIESTA BRILLANTE BOLSA EXTRA GRANDE ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Extra Grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 45
  },
  {
    "codigo": "682158032197",
    "descripcion": "3137 FIESTA BRILLANTE BOLSA GRANDE ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 45
  },
  {
    "codigo": "682158032203",
    "descripcion": "3138 FIESTA BRILLANTE BOLSA MEDIANA ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 45
  },
  {
    "codigo": "682158032210",
    "descripcion": "3139 FIESTA BRILLANTE BOLSA CHICA ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 45
  },
  {
    "codigo": "682158032302",
    "descripcion": "3137 PIXIE BOLSA GRANDE ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 45
  },
  {
    "codigo": "682158032319",
    "descripcion": "3138 PIXIE BOLSA MEDIANA ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 45
  },
  {
    "codigo": "682158032326",
    "descripcion": "3139 PIXIE BOLSA CHICA ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 45
  },
  {
    "codigo": "682158031565",
    "descripcion": "3139 PINCELADAS BOLSA CHICA ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 46
  },
  {
    "codigo": "682158032265",
    "descripcion": "3137 BABY ALGODON GRANDE ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 46
  },
  {
    "codigo": "682158032272",
    "descripcion": "3138 BABY ALGODON MEDIANA ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 46
  },
  {
    "codigo": "682158032289",
    "descripcion": "3137 BABY CELESTE BOLSA GRANDE ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 46
  },
  {
    "codigo": "682158032296",
    "descripcion": "3138 BABY CELESTE BOLSA MEDIANA ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 46
  },
  {
    "codigo": "682158031404",
    "descripcion": "3136 ARCOIRIS BOLSA EXTRA GRANDE ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Extra Grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 47
  },
  {
    "codigo": "682158031435",
    "descripcion": "3139 ARCOIRIS BOLSA CHICA ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 47
  },
  {
    "codigo": "682158031497",
    "descripcion": "3139 VENUS BOLSA CHICA ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 47
  },
  {
    "codigo": "682158032227",
    "descripcion": "3136 NEPTUNO BOLSA EXTRA GRANDE ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Extra Grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 47
  },
  {
    "codigo": "682158032234",
    "descripcion": "3137 NEPTUNO BOLSA GRANDE ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 47
  },
  {
    "codigo": "682158032241",
    "descripcion": "3138 NEPTUNO BOLSA MEDIANA ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 47
  },
  {
    "codigo": "682158032258",
    "descripcion": "3139 NEPTUNO BOLSA CHICA ESTAMPADO ESPECIAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Estampada Chica",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 47
  },
  {
    "codigo": "682158020439",
    "descripcion": "1846 HERMOSA FLOR BOLSA EXTRA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 49
  },
  {
    "codigo": "682158020453",
    "descripcion": "0375 HERMOSA FLOR BOLSA MEDIANA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa mediana",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 49
  },
  {
    "codigo": "682158026011",
    "descripcion": "1846 PERFUME BOLSA EXTRA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 49
  },
  {
    "codigo": "682158026035",
    "descripcion": "1846 PRADERA BOLSA EXTRA GRANDE FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Bolsa extra grande",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 49
  },
  {
    "codigo": "682158004129",
    "descripcion": "412 PAPEL ENVOLTURA JUVENIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 53
  },
  {
    "codigo": "682158203108",
    "descripcion": "310 PAPEL ENVOLTURA JUVENIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 53
  },
  {
    "codigo": "682158203436",
    "descripcion": "343 PAPEL ENVOLTURA JUVENIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 53
  },
  {
    "codigo": "682158220907",
    "descripcion": "22090 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 53
  },
  {
    "codigo": "682158204709",
    "descripcion": "470 PAPEL ENVOLTURA JUVENIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 54
  },
  {
    "codigo": "682158204716",
    "descripcion": "471 PAPEL ENVOLTURA JUVENIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 54
  },
  {
    "codigo": "682158205218",
    "descripcion": "521 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 54
  },
  {
    "codigo": "682158205225",
    "descripcion": "522 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 54
  },
  {
    "codigo": "682158220914",
    "descripcion": "22091 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 54
  },
  {
    "codigo": "682158220921",
    "descripcion": "22092 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 54
  },
  {
    "codigo": "682158205294",
    "descripcion": "529 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 55
  },
  {
    "codigo": "682158206512",
    "descripcion": "651 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 55
  },
  {
    "codigo": "682158207977",
    "descripcion": "797 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 55
  },
  {
    "codigo": "682158220938",
    "descripcion": "22093 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 55
  },
  {
    "codigo": "682158220945",
    "descripcion": "22094 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 55
  },
  {
    "codigo": "682158208813",
    "descripcion": "881 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 56
  },
  {
    "codigo": "682158208820",
    "descripcion": "882 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 56
  },
  {
    "codigo": "682158209995",
    "descripcion": "999 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 56
  },
  {
    "codigo": "682158220228",
    "descripcion": "22022 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 56
  },
  {
    "codigo": "682158220952",
    "descripcion": "22095 PAPEL ENVOLTURA CUMPLEAÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 56
  },
  {
    "codigo": "682158202415",
    "descripcion": "241 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 57
  },
  {
    "codigo": "682158202422",
    "descripcion": "242 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 57
  },
  {
    "codigo": "682158202989",
    "descripcion": "298 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 57
  },
  {
    "codigo": "682158221072",
    "descripcion": "22107 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 57
  },
  {
    "codigo": "682158221089",
    "descripcion": "22108 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 57
  },
  {
    "codigo": "682158207779",
    "descripcion": "777 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 58
  },
  {
    "codigo": "682158208943",
    "descripcion": "894 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 58
  },
  {
    "codigo": "682158208950",
    "descripcion": "895 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 58
  },
  {
    "codigo": "682158220105",
    "descripcion": "22010 PAPEL ENVOLTURA TEXTURA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 58
  },
  {
    "codigo": "682158221096",
    "descripcion": "22109 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 58
  },
  {
    "codigo": "682158221102",
    "descripcion": "22110 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 58
  },
  {
    "codigo": "682158220112",
    "descripcion": "22011 PAPEL ENVOLTURA TEXTURA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 59
  },
  {
    "codigo": "682158220129",
    "descripcion": "22012 PAPEL ENVOLTURA TEXTURA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 59
  },
  {
    "codigo": "682158220143",
    "descripcion": "22014 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 59
  },
  {
    "codigo": "682158220150",
    "descripcion": "22015 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 59
  },
  {
    "codigo": "682158220167",
    "descripcion": "22016 PAPEL ENVOLTURA TEXTURAS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 59
  },
  {
    "codigo": "682158208837",
    "descripcion": "883 PAPEL ENVOLTURA FLORAL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 60
  },
  {
    "codigo": "682158208844",
    "descripcion": "884 PAPEL ENVOLTURA FLORAL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 60
  },
  {
    "codigo": "682158220969",
    "descripcion": "22096 PAPEL ENVOLTURA FLORAL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 60
  },
  {
    "codigo": "682158220976",
    "descripcion": "22097 PAPEL ENVOLTURA FLORAL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 60
  },
  {
    "codigo": "682158220037",
    "descripcion": "22003 PAPEL ENVOLTURA FLORAL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 61
  },
  {
    "codigo": "682158220044",
    "descripcion": "22004 PAPEL ENVOLTURA FLORAL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 61
  },
  {
    "codigo": "682158220051",
    "descripcion": "22005 PAPEL ENVOLTURA FLORAL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 61
  },
  {
    "codigo": "682158220068",
    "descripcion": "22006 PAPEL ENVOLTURA FLORAL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 61
  },
  {
    "codigo": "682158220990",
    "descripcion": "22099 PAPEL ENVOLTURA FLORAL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 61
  },
  {
    "codigo": "682158202705",
    "descripcion": "270 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 62
  },
  {
    "codigo": "682158202712",
    "descripcion": "271 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 62
  },
  {
    "codigo": "682158203191",
    "descripcion": "319 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 62
  },
  {
    "codigo": "682158220075",
    "descripcion": "22007 PAPEL ENVOLTURA FLORAL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 62
  },
  {
    "codigo": "682158220136",
    "descripcion": "22013 PAPEL ENVOLTURA FLORAL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 62
  },
  {
    "codigo": "682158221003",
    "descripcion": "22100 PAPEL ENVOLTURA FLORAL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 62
  },
  {
    "codigo": "682158204624",
    "descripcion": "462 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 63
  },
  {
    "codigo": "682158204631",
    "descripcion": "463 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 63
  },
  {
    "codigo": "682158204808",
    "descripcion": "480 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 63
  },
  {
    "codigo": "682158205270",
    "descripcion": "527 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 63
  },
  {
    "codigo": "682158221034",
    "descripcion": "22103 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 63
  },
  {
    "codigo": "682158221041",
    "descripcion": "22104 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 63
  },
  {
    "codigo": "682158205300",
    "descripcion": "530 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 64
  },
  {
    "codigo": "682158205317",
    "descripcion": "531 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 64
  },
  {
    "codigo": "682158205324",
    "descripcion": "532 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 64
  },
  {
    "codigo": "682158206598",
    "descripcion": "659 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 64
  },
  {
    "codigo": "682158221058",
    "descripcion": "22105 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 64
  },
  {
    "codigo": "682158221065",
    "descripcion": "22106 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 64
  },
  {
    "codigo": "682158206604",
    "descripcion": "660 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 65
  },
  {
    "codigo": "682158206611",
    "descripcion": "661 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 65
  },
  {
    "codigo": "682158206710",
    "descripcion": "671 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 65
  },
  {
    "codigo": "682158207861",
    "descripcion": "786 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 65
  },
  {
    "codigo": "682158208851",
    "descripcion": "885 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 66
  },
  {
    "codigo": "682158208868",
    "descripcion": "886 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 66
  },
  {
    "codigo": "682158208875",
    "descripcion": "887 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 66
  },
  {
    "codigo": "682158209964",
    "descripcion": "996 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 66
  },
  {
    "codigo": "682158209988",
    "descripcion": "998 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 66
  },
  {
    "codigo": "682158220211",
    "descripcion": "22021 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 66
  },
  {
    "codigo": "682158202316",
    "descripcion": "231 PAPEL ENVOLTURA BEBE 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 67
  },
  {
    "codigo": "682158202323",
    "descripcion": "232 PAPEL ENVOLTURA BEBE 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 67
  },
  {
    "codigo": "682158204099",
    "descripcion": "409 PAPEL ENVOLTURA BEBE 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 67
  },
  {
    "codigo": "682158204235",
    "descripcion": "423 PAPEL ENVOLTURA BEBE 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 67
  },
  {
    "codigo": "682158221010",
    "descripcion": "22101 PAPEL ENVOLTURA BEBE 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 67
  },
  {
    "codigo": "682158221027",
    "descripcion": "22102 PAPEL ENVOLTURA BEBE 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 67
  },
  {
    "codigo": "682158204976",
    "descripcion": "497 PAPEL ENVOLTURA BEBE 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 68
  },
  {
    "codigo": "682158208882",
    "descripcion": "888 PAPEL ENVOLTURA BEBE 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 68
  },
  {
    "codigo": "682158208899",
    "descripcion": "889 PAPEL ENVOLTURA BEBE 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 68
  },
  {
    "codigo": "682158220013",
    "descripcion": "22001 PAPEL ENVOLTURA BEBE 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 68
  },
  {
    "codigo": "682158220020",
    "descripcion": "22002 PAPEL ENVOLTURA BEBE 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 68
  },
  {
    "codigo": "682158220198",
    "descripcion": "22019 PAPEL ENVOLTURA BEBÉS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 68
  },
  {
    "codigo": "682158208981",
    "descripcion": "898 PAPEL ENVOLTURA PRIMERA COMUNION 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 69
  },
  {
    "codigo": "682158209018",
    "descripcion": "901 PAPEL ENVOLTURA XV AÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 69
  },
  {
    "codigo": "682158220204",
    "descripcion": "22020 PAPEL ENVOLTURA BEBÉS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 69
  },
  {
    "codigo": "682158220259",
    "descripcion": "22025 PAPEL ENVOLTURA 1RA COMUNIÓN 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 69
  },
  {
    "codigo": "682158221126",
    "descripcion": "22112 PAPEL ENVOLTURA COMUNION 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 69
  },
  {
    "codigo": "682158011611",
    "descripcion": "219 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 70
  },
  {
    "codigo": "682158202033",
    "descripcion": "203 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 70
  },
  {
    "codigo": "682158202330",
    "descripcion": "233 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 70
  },
  {
    "codigo": "682158202477",
    "descripcion": "247 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 70
  },
  {
    "codigo": "682158220235",
    "descripcion": "22023 PAPEL ENVOLTURA XV AÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 70
  },
  {
    "codigo": "682158220242",
    "descripcion": "22024 PAPEL ENVOLTURA XV AÑOS 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 70
  },
  {
    "codigo": "682158202507",
    "descripcion": "250 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 71
  },
  {
    "codigo": "682158202538",
    "descripcion": "253 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 71
  },
  {
    "codigo": "682158203726",
    "descripcion": "372 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 71
  },
  {
    "codigo": "682158204174",
    "descripcion": "417 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 71
  },
  {
    "codigo": "682158204754",
    "descripcion": "475 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 71
  },
  {
    "codigo": "682158204839",
    "descripcion": "483 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 72
  },
  {
    "codigo": "682158204938",
    "descripcion": "493 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 72
  },
  {
    "codigo": "682158208929",
    "descripcion": "892 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 72
  },
  {
    "codigo": "682158208936",
    "descripcion": "893 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 72
  },
  {
    "codigo": "682158221119",
    "descripcion": "22111 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 72
  },
  {
    "codigo": "682158206505",
    "descripcion": "650 PAPEL ENVOLTURA GRADUACION 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 73
  },
  {
    "codigo": "682158207908",
    "descripcion": "790 PAPEL ENVOLTURA GRADUACIÓN 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 73
  },
  {
    "codigo": "682158220082",
    "descripcion": "22008 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 73
  },
  {
    "codigo": "682158220099",
    "descripcion": "22009 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 73
  },
  {
    "codigo": "682158220174",
    "descripcion": "22017 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 73
  },
  {
    "codigo": "682158220181",
    "descripcion": "22018 PAPEL ENVOLTURA BODA 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 73
  },
  {
    "codigo": "682158203184",
    "descripcion": "318 PAPEL ENVOLTURA CABALLERO 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 74
  },
  {
    "codigo": "682158203603",
    "descripcion": "360 PAPEL ENVOLTURA CABALLERO 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 74
  },
  {
    "codigo": "682158208967",
    "descripcion": "896 PAPEL ENVOLTURA GRADUACION 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 74
  },
  {
    "codigo": "682158208974",
    "descripcion": "897 PAPEL ENVOLTURA GRADUACION 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 74
  },
  {
    "codigo": "682158209940",
    "descripcion": "994 PAPEL ENVOLTURA GRADUACIÓN 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 74
  },
  {
    "codigo": "682158209957",
    "descripcion": "995 PAPEL ENVOLTURA GRADUACIÓN 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 74
  },
  {
    "codigo": "682158204662",
    "descripcion": "466 PAPEL ENVOLTURA CABALLERO 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 75
  },
  {
    "codigo": "682158204952",
    "descripcion": "495 PAPEL ENVOLTURA CABALLERO 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 75
  },
  {
    "codigo": "682158206666",
    "descripcion": "666 PAPEL ENVOLTURA CABALLERO 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 75
  },
  {
    "codigo": "682158208028",
    "descripcion": "802 PAPEL ENVOLTURA CABALLERO 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 75
  },
  {
    "codigo": "682158221133",
    "descripcion": "22113 PAPEL ENVOLTURA CABALLERO 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 75
  },
  {
    "codigo": "682158221140",
    "descripcion": "22114 PAPEL ENVOLTURA CABALLERO 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 75
  },
  {
    "codigo": "682158209001",
    "descripcion": "900 PAPEL ENVOLTURA CABALLERO 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 76
  },
  {
    "codigo": "682158220266",
    "descripcion": "22026 PAPEL ENVOLTURA CABALLERO 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 76
  },
  {
    "codigo": "682158220273",
    "descripcion": "22027 PAPEL ENVOLTURA CABALLERO 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 76
  },
  {
    "codigo": "682158221157",
    "descripcion": "22115 PAPEL ENVOLTURA CABALLERO 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 76
  },
  {
    "codigo": "682158010591",
    "descripcion": "1059 LETRERO FELIZ CUMPLEAÑOS MULTICOLOR",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 77
  },
  {
    "codigo": "682158029609",
    "descripcion": "2960 LETRERO CUMPLEAÑOS CARNAVAL",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 77
  },
  {
    "codigo": "682158209971",
    "descripcion": "997 PAPEL ENVOLTURA INFANTIL 0435",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 77
  },
  {
    "codigo": "682158010621",
    "descripcion": "1062 LETRERO CUMPLEAÑOS FESTIVO",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 78
  },
  {
    "codigo": "682158010683",
    "descripcion": "1068 LETRERO CUANTOS CUMPLES",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 78
  },
  {
    "codigo": "682158023010",
    "descripcion": "2301 LETRERO FELIZ CUMPLEAÑOS GLOBO",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 78
  },
  {
    "codigo": "682158011369",
    "descripcion": "1136 BANDERIN FELICIDADES",
    "linea": "Toda ocasión",
    "categoria": "Letrero banderin",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 79
  },
  {
    "codigo": "682158018405",
    "descripcion": "1840 BANDERIN TRIANGULAR NEON",
    "linea": "Toda ocasión",
    "categoria": "Letrero banderin",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 79
  },
  {
    "codigo": "682158018498",
    "descripcion": "1849 LETRERO MEGA CUMPLEAÑOS NEON",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 79
  },
  {
    "codigo": "682158020279",
    "descripcion": "2027 LETRERO MEGA FIESTEROS",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 80
  },
  {
    "codigo": "682158023669",
    "descripcion": "2366 LETRERO MEGA FELICIDADES",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 81
  },
  {
    "codigo": "682158025717",
    "descripcion": "2571 BANDERIN FIESTA",
    "linea": "Toda ocasión",
    "categoria": "Letrero banderin",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 81
  },
  {
    "codigo": "682158025694",
    "descripcion": "2569 LETRERO MEGA FLORAL",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 82
  },
  {
    "codigo": "682158025892",
    "descripcion": "2589 LETRERO BIENVENIDO MULTIPLE",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 83
  },
  {
    "codigo": "682158031640",
    "descripcion": "3164 LETRERO CUMPLEAÑOS DIVERTIDO",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 83
  },
  {
    "codigo": "682158030964",
    "descripcion": "3096 PÓSTER MAXI NIÑO GRADUADO",
    "linea": "Toda ocasión",
    "categoria": "Póster",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 84
  },
  {
    "codigo": "682158030971",
    "descripcion": "3097 PÓSTER MAXI NIÑA GRADUADA",
    "linea": "Toda ocasión",
    "categoria": "Póster",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 84
  },
  {
    "codigo": "682158015596",
    "descripcion": "1559 BANDERIN GRADUACION",
    "linea": "Toda ocasión",
    "categoria": "Letrero banderin",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 85
  },
  {
    "codigo": "682158031336",
    "descripcion": "3133 BANDERIN GRADUAKIDS",
    "linea": "Toda ocasión",
    "categoria": "Letrero banderin",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 85
  },
  {
    "codigo": "682158031350",
    "descripcion": "3135 LETRERO GRADUACION DORADO",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 85
  },
  {
    "codigo": "682158018511",
    "descripcion": "1851 BIRRETES DECORATIVOS PAQ. 4 PZ",
    "linea": "Escolar",
    "categoria": "Articulos varios",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 86
  },
  {
    "codigo": "682158031343",
    "descripcion": "3134 LETRERO BABY SHOWER GLOBO",
    "linea": "Toda ocasión",
    "categoria": "Letrero",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 87
  },
  {
    "codigo": "682158029272",
    "descripcion": "2927 SOBRES PARA DINERO COLOR PAQ 24PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 88
  },
  {
    "codigo": "682158032173",
    "descripcion": "3217 SOBRES PARA DINERO COLOR 2 PAQ. 24 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 88
  },
  {
    "codigo": "682158022853",
    "descripcion": "2285 CAJA CUBO DIVERTI FIESTA 15",
    "linea": "Toda ocasión",
    "categoria": "Caja impresa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 89
  },
  {
    "codigo": "682158020552",
    "descripcion": "2055 CAJA CUBO PETALOS FLORAL 15",
    "linea": "Toda ocasión",
    "categoria": "Caja impresa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 90
  },
  {
    "codigo": "682158023003",
    "descripcion": "2300 CAJA CUBO PETALOS FLORAL 30",
    "linea": "Toda ocasión",
    "categoria": "Caja impresa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 90
  },
  {
    "codigo": "682158025021",
    "descripcion": "2502 CAJA CUBO KIDS INFANTIL 22",
    "linea": "Toda ocasión",
    "categoria": "Caja impresa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 90
  },
  {
    "codigo": "682158029128",
    "descripcion": "2912 CAJA CUBO MELODIA (22) MICROCORRUGADA",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 91
  },
  {
    "codigo": "682158029135",
    "descripcion": "2913 CAJA CUBO MELODIA (15) MICROCORRUGADA",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 91
  },
  {
    "codigo": "682158029623",
    "descripcion": "2962 CAJA CUBO CUMPLEAÑOS (22) MICROCORRUGADA",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 91
  },
  {
    "codigo": "682158029630",
    "descripcion": "2963 CAJA CUBO CUMPLEAÑOS (15) MICROCORRUGADA",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 91
  },
  {
    "codigo": "682158029821",
    "descripcion": "2982 CAJA CUBO CUMPLEAÑOS (30) MICROCORRUGADA",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 91
  },
  {
    "codigo": "682158031619",
    "descripcion": "3161 CAJA CUBO MELODIA (30) MICROCORRUGADA",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 91
  },
  {
    "codigo": "682158031626",
    "descripcion": "3162 CAJA CUBO FOSFO (15) MICROCORRUGADA PAQ C/8 PZS",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 92
  },
  {
    "codigo": "682158032449",
    "descripcion": "3244 CAJA CUBO FOSFO (22) MICROCORRUGADA PAQ C/8 PZS",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 92
  },
  {
    "codigo": "682158033170",
    "descripcion": "3317 CAJA CUBO FIESTA (30) MICROCORRUGADA PAQ C/8 PZS",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 92
  },
  {
    "codigo": "682158033187",
    "descripcion": "3318 CAJA CUBO FIESTA (22) MICROCORRUGADA",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 92
  },
  {
    "codigo": "682158033194",
    "descripcion": "3319 CAJA CUBO FIESTA (15) MICROCORRUGADA",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 92
  },
  {
    "codigo": "682158029104",
    "descripcion": "2910 CAJA CUBO NATURAL (22) MICROCORRUGADA",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 93
  },
  {
    "codigo": "682158029111",
    "descripcion": "2911 CAJA CUBO NATURAL (15) MICROCORRUGADA",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 93
  },
  {
    "codigo": "682158031138",
    "descripcion": "3113 CAJA CUBO NEGRA (30) MICROCORRUGADA PAQ C/8 PZS",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 93
  },
  {
    "codigo": "682158031572",
    "descripcion": "3157 CAJA CUBO NEGRA (22) MICROCORRUGADA PAQ C/8 PZS",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 93
  },
  {
    "codigo": "682158031589",
    "descripcion": "3158 CAJA CUBO NEGRA (15) MICROCORRUGADA PAQ C/8 PZS",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 93
  },
  {
    "codigo": "682158032562",
    "descripcion": "3256 CAJA CUBO ROJA (30) MICROCORRUGADA PAQ C/8 PZS",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 93
  },
  {
    "codigo": "682158032579",
    "descripcion": "3257 CAJA CUBO ROJA (22) MICROCORRUGADA PAQ C/8 PZS",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 93
  },
  {
    "codigo": "682158032982",
    "descripcion": "3298 CAJA CUBO ROJA (15) MICROCORRUGADA PAQ C/8 PZS",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 93
  },
  {
    "codigo": "682158027735",
    "descripcion": "2773 CAJA ESTAMPADA GLAMOUR NO. 3 5 PZS X PAQ.",
    "linea": "Toda ocasión",
    "categoria": "Caja impresa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 94
  },
  {
    "codigo": "682158027742",
    "descripcion": "2774 CAJA ESTAMPADA GLAMOUR NO. 10 5 PZS X PAQ.",
    "linea": "Toda ocasión",
    "categoria": "Caja impresa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 94
  },
  {
    "codigo": "682158027759",
    "descripcion": "2775 CAJA ESTAMPADA GLAMOUR PM 5 PZS X PAQ.",
    "linea": "Toda ocasión",
    "categoria": "Caja impresa",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 94
  },
  {
    "codigo": "682158031633",
    "descripcion": "3163 CAJA CUBO DELUXE (22) MICROCORRUGADA PAQ C/8 PZS",
    "linea": "Toda ocasión",
    "categoria": "Caja microcorrugado",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 94
  },
  {
    "codigo": "682158013738",
    "descripcion": "1373 ALFABETO MOVIL                                 Escolar",
    "linea": "Escolar",
    "categoria": "es",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 95
  },
  {
    "codigo": "682158014490",
    "descripcion": "1449 ALFABETO MOVIL MINI                            Escolar",
    "linea": "Escolar",
    "categoria": "es",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 95
  },
  {
    "codigo": "682158001230",
    "descripcion": "0123 TABLA PERIODICA GRANDE UV PAQ. 25 PZ                    Escolar",
    "linea": "Escolar",
    "categoria": "es",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 96
  },
  {
    "codigo": "682158001247",
    "descripcion": "0124 TABLA PERIODICA CHICA UV PAQ. 25 PZ                     Escolar",
    "linea": "Escolar",
    "categoria": "es",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 96
  },
  {
    "codigo": "682158002190",
    "descripcion": "0219 ALBUM DE CUERPOS GEOMÉTRICOS                            Escolar",
    "linea": "Escolar",
    "categoria": "es",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 96
  },
  {
    "codigo": "682158013714",
    "descripcion": "1371 CUERPOS GEOMÉTRICOS ARMABLES                   Escolar",
    "linea": "Escolar",
    "categoria": "es",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 96
  },
  {
    "codigo": "682158006662",
    "descripcion": "0666 HOJA BOND BLANCO TAMAÑO CARTA 75 GR PAQ/100 PZS",
    "linea": "Escolar",
    "categoria": "Papel bond a color paq. 100",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "682158013462",
    "descripcion": "1346 HOJA DE COLOR NEGRO PAQ. 100 PZ",
    "linea": "Escolar",
    "categoria": "Papel bond a color paq. 100",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "682158022372",
    "descripcion": "2237 CARTULINA NEGRA PAQ. 25",
    "linea": "Escolar",
    "categoria": "Cartulina",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "682158026653",
    "descripcion": "2665 CARTULINA AZUL OSCURO PAQ. 25",
    "linea": "Escolar",
    "categoria": "Cartulina",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "682158026660",
    "descripcion": "2666 CARTULINA AZUL CIELO PAQ. 25",
    "linea": "Escolar",
    "categoria": "Cartulina",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "682158026684",
    "descripcion": "2668 CARTULINA ROJO PAQ. 25",
    "linea": "Escolar",
    "categoria": "Cartulina",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "682158026721",
    "descripcion": "2672 CARTULINA VERDE PERICO PAQ. 25",
    "linea": "Escolar",
    "categoria": "Cartulina",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "682158026752",
    "descripcion": "2675 CARTULINA ROSA PAQ. 25",
    "linea": "Escolar",
    "categoria": "Cartulina",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "682158029357",
    "descripcion": "2935 CARTULINA VERDE FLUORESCENTE PAQ. 25",
    "linea": "Escolar",
    "categoria": "Cartulina",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "682158029364",
    "descripcion": "2936 CARTULINA AMARILLO FLUORESCENTE PAQ. 25",
    "linea": "Escolar",
    "categoria": "Cartulina",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "682158029371",
    "descripcion": "2937 CARTULINA NARANJA FLUORESCENTE PAQ. 25",
    "linea": "Escolar",
    "categoria": "Cartulina",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "682158029388",
    "descripcion": "2938 CARTULINA ROSA FLUORESCENTE PAQ. 25",
    "linea": "Escolar",
    "categoria": "Cartulina",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "682158029524",
    "descripcion": "2952 HOJA DE COLOR NARANJA FLUORESCENTE",
    "linea": "Escolar",
    "categoria": "Papel bond a color paq. 100",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 97
  },
  {
    "codigo": "68215800065301",
    "descripcion": "065 SELLO AUTOADHERIBLE REDONDO ORO PAQ. 25 PLANILLAS",
    "linea": "Escolar",
    "categoria": "sello",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 98
  },
  {
    "codigo": "68215800065302",
    "descripcion": "065 SELLO AUTOADHERIBLE REDONDO PLATA PAQ. 25 PLANILLAS",
    "linea": "Escolar",
    "categoria": "sello",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 98
  },
  {
    "codigo": "68215800066001",
    "descripcion": "066 SELLO AUTOADHERIBLE ESTRELLA ORO PAQ. 25 PZ",
    "linea": "Escolar",
    "categoria": "sello",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 98
  },
  {
    "codigo": "68215800312803",
    "descripcion": "0312 SELLO AUTOADHERIBLE ESTRELLAS COLOR PAQ. 25 PZ",
    "linea": "Escolar",
    "categoria": "sello",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 98
  },
  {
    "codigo": "682158000790",
    "descripcion": "0079 SOBRE CHICO COLOR SURTIDO (14X9.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 99
  },
  {
    "codigo": "682158000806",
    "descripcion": "0080 SOBRE GRANDE COLOR SURTIDO (20.2X13.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 99
  },
  {
    "codigo": "682158000813",
    "descripcion": "0081 SOBRE MEDIANO COLOR SURTIDO (17X11.8) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 99
  },
  {
    "codigo": "682158000820",
    "descripcion": "0082 SOBRE MINI COLOR SURTIDO (9.3X6) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 99
  },
  {
    "codigo": "682158000837",
    "descripcion": "0083 SOBRE MINI BLANCO (9.3X6) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 99
  },
  {
    "codigo": "682158002916",
    "descripcion": "0291 SOBRE CARTA COLOR SURTIDO (16.5X9.3) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 99
  },
  {
    "codigo": "682158007058",
    "descripcion": "0705 SELLO AUTOADHERIBLE ESTRELLITAS ARCOIRIS 2 PLANILLAS",
    "linea": "Escolar",
    "categoria": "sello",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 99
  },
  {
    "codigo": "682158031596",
    "descripcion": "3159 SELLO AUTOADHERIBLE ESTRELLITAS TORNASOL PAQ. C/2 PLANILLAS",
    "linea": "Escolar",
    "categoria": "sello",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 99
  },
  {
    "codigo": "682158013813",
    "descripcion": "1381 SOBRE MINI KRAFT (9.3X6) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 100
  },
  {
    "codigo": "682158029753",
    "descripcion": "2975 SOBRE BOND BLANCO PARA TARJETA (13.5X20.5) PAQ C/100 PZS",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 100
  },
  {
    "codigo": "682158029760",
    "descripcion": "2976 SOBRE BOND BLANCO INVITACIÓN (9X14) PAQ C/100 PZS",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 100
  },
  {
    "codigo": "682158031695",
    "descripcion": "3169 SOBRE MINI NARANJA (9.3X6.0) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 101
  },
  {
    "codigo": "682158031718",
    "descripcion": "3171 SOBRE MINI ROSA FLUORESCENTE (9.3X6.0) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 101
  },
  {
    "codigo": "682158031756",
    "descripcion": "3175 SOBRE MINI ROJO (9.3X6.0) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 101
  },
  {
    "codigo": "682158031787",
    "descripcion": "3178 SOBRE MINI PLATA (9.3X6.0) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 101
  },
  {
    "codigo": "682158000059",
    "descripcion": "0005 BOLSA TRANSP CHICA (10X28.8+6) PAQ 25 PZ",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Celofán",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158000066",
    "descripcion": "0006 BOLSA TRANSP MEDIANA (16X36+13) PAQ 25 PZ",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Celofán",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158000073",
    "descripcion": "0007 BOLSA TRANSP GRANDE (20X58+13) PAQ 25 PZ",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Celofán",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158000424",
    "descripcion": "0042 SOBRE TRANSP SIN ADHESIVO (12.8X8.5+3) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158000431",
    "descripcion": "0043 SOBRE TRANSP SIN ADHESIVO (14.5X10.5+3.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158000448",
    "descripcion": "0044 SOBRE TRANSP SIN ADHESIVO (16.5X11.5+3) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158000561",
    "descripcion": "0056 SOBRE TRANSP SIN ADHESIVO (23.5X16+3.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158002985",
    "descripcion": "0298 SOBRE TRANSP SIN ADHESIVO (22.5X16+3.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158003067",
    "descripcion": "0306 SOBRE TRANSP SIN ADHESIVO (13X13+3) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158003487",
    "descripcion": "0348 BOLSA TRANSP EXTRA GRANDE (30X70+20) PAQ 25 PZ",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Celofán",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158003494",
    "descripcion": "0349 BOLSA TRANSP JUMBO (40X81+11) PAQ 25 PZ",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Celofán",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158004361",
    "descripcion": "0436 SOBRE TRANSP SIN ADHESIVO (9.7X6.5+3.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158004972",
    "descripcion": "0497 BOLSA TRANSP DULCERA (8.5X21.5+6) PAQ 25 PZ",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Celofán",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158005610",
    "descripcion": "0561 SOBRE TRANSP CON ADHESIVO (19.3X19.3+4.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158005627",
    "descripcion": "0562 SOBRE TRANSP CON ADHESIVO (22.2X14.5+4.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158005634",
    "descripcion": "0563 SOBRE TRANSP CON ADHESIVO (11X17+4.5) PAQ. 100PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158005641",
    "descripcion": "0564 SOBRE TRANSP CON ADHESIVO (12X21+4.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158005658",
    "descripcion": "0565 SOBRE TRANSP CON ADHESIVO (7.5X11+4.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158005665",
    "descripcion": "0566 SOBRE TRANSP CON ADHESIVO (14X15+4.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158005672",
    "descripcion": "0567 SOBRE TRANSP CON ADHESIVO (10X14+4.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158029074",
    "descripcion": "2907 SOBRE TRANSP SIN ADHESIVO (7.5X11+3.5) PAQ. 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Sobre celofan",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158030575",
    "descripcion": "3057 BOLSA TRANSP MICRO CON FUELLE (6+3x14) PAQ 25 PZ",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Celofán",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "682158030605",
    "descripcion": "3060 BOLSA TRANSP SIN FUELLE (5X20) PAQ 100 PZ",
    "linea": "Toda ocasión",
    "categoria": "Bolsa Celofán",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 102
  },
  {
    "codigo": "681258080020",
    "descripcion": "8002 CAJA BLANCA (0) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080037",
    "descripcion": "8003 CAJA BLANCA (2) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080044",
    "descripcion": "8004 CAJA BLANCA (3) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080068",
    "descripcion": "8006 CAJA BLANCA (5) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080075",
    "descripcion": "8007 CAJA BLANCA (26) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080082",
    "descripcion": "8008 CAJA BLANCA (28) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080099",
    "descripcion": "8009 CAJA BLANCA (30) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080105",
    "descripcion": "8010 CAJA BLANCA COBERTOR 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080112",
    "descripcion": "8011 CAJA BLANCA (9) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080129",
    "descripcion": "8012 CAJA BLANCA (10) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080136",
    "descripcion": "8013 CAJA BLANCA PASTEL 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080143",
    "descripcion": "8014 CAJA BLANCA PM 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080150",
    "descripcion": "8015 CAJA BLANCA PELUCHE 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080501",
    "descripcion": "8050 CAJA BLANCA (1) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080518",
    "descripcion": "8051 CAJA BLANCA (24) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080525",
    "descripcion": "8052 CAJA BLANCA (3) ESPECIAL 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080532",
    "descripcion": "8053 CAJA BLANCA (26) ESPECIAL 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080549",
    "descripcion": "8054 CAJA BLANCA NOVIA 12 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080594",
    "descripcion": "8059 CAJA BALON 12 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080686",
    "descripcion": "8068 CAJA BLANCA LONCHERA 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080693",
    "descripcion": "8069 CAJA BLANCA (27) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080709",
    "descripcion": "8070 CAJA BLANCA (11) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "681258080716",
    "descripcion": "8071 CAJA BLANCA (12) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "682158080761",
    "descripcion": "8076 CAJA BLANCA (40) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "682158080792",
    "descripcion": "8079 CAJA CUBO (30) 12 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "682158080853",
    "descripcion": "8085 CAJA CUBO (10) 12 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "682158080860",
    "descripcion": "8086 CAJA CUBO (15) 12 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "682158080945",
    "descripcion": "8094 CAJA BLANCA (14) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "682158080976",
    "descripcion": "8097 CAJA BLANCA (16) 10 PZ X PAQ",
    "linea": "Toda ocasión",
    "categoria": "caja blanca",
    "en_catalogo": "Sí",
    "catalogo": "Toda Ocasión",
    "pagina": 103
  },
  {
    "codigo": "682158221300",
    "descripcion": "PAPEL TODA OCASIÓN 22130",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "No",
    "catalogo": "",
    "pagina": ""
  },
  {
    "codigo": "682158221317",
    "descripcion": "PAPEL TODA OCASIÓN 22131",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "No",
    "catalogo": "",
    "pagina": ""
  },
  {
    "codigo": "682158221324",
    "descripcion": "PAPEL TODA OCASIÓN 22132",
    "linea": "Toda ocasión",
    "categoria": "Papel envoltura couche",
    "en_catalogo": "No",
    "catalogo": "",
    "pagina": ""
  }
];
        
        // Estado de la aplicación
        let cart = {};
        let currentFilter = 'all';
        let searchTerm = '';
        let pageFilter = null;
        
        // Cargar carrito desde localStorage
        function loadCart() {
            const saved = localStorage.getItem('padcolor_cart');
            if (saved) {
                try {
                    cart = JSON.parse(saved);
                    renderCart();
                } catch (e) {
                    console.error('Error loading cart:', e);
                }
            }
        }
        
        // Guardar carrito en localStorage
        function saveCart() {
            localStorage.setItem('padcolor_cart', JSON.stringify(cart));
        }
        
        // Cargar datos del cliente
        function loadClientData() {
            const clientCode = localStorage.getItem('padcolor_client_code');
            const clientName = localStorage.getItem('padcolor_client_name');
            const orderDate = localStorage.getItem('padcolor_order_date');
            
            if (clientCode) document.getElementById('clientCode').value = clientCode;
            if (clientName) document.getElementById('clientName').value = clientName;
            if (orderDate) document.getElementById('orderDate').value = orderDate;
        }
        
        // Guardar datos del cliente
        function saveClientData() {
            localStorage.setItem('padcolor_client_code', document.getElementById('clientCode').value);
            localStorage.setItem('padcolor_client_name', document.getElementById('clientName').value);
            localStorage.setItem('padcolor_order_date', document.getElementById('orderDate').value);
        }
        
        // Inicializar fecha actual
        function initDate() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('orderDate').value = today;
        }
        
        // Obtener líneas únicas
        function getLineas() {
            const lineas = [...new Set(PRODUCTOS_DB.map(p => p.linea).filter(Boolean))];
            return lineas.sort();
        }
        
        // Renderizar filtros
        function renderFilters() {
            const container = document.getElementById('filterButtons');
            const lineas = getLineas();
            
            let html = '<button class="filter-btn active" data-filter="all">📦 Todos</button>';
            lineas.forEach(linea => {
                const icon = linea === 'Escolar' ? '📚' : '🎁';
                html += `<button class="filter-btn" data-filter="${linea}">${icon} ${linea}</button>`;
            });
            
            container.innerHTML = html;
            
            // Event listeners
            container.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    currentFilter = btn.dataset.filter;
                    container.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    renderProducts();
                });
            });
        }
        
        // Filtrar productos
        function filterProducts() {
            let filtered = PRODUCTOS_DB;
            
            // Filtrar por línea
            if (currentFilter !== 'all') {
                filtered = filtered.filter(p => p.linea === currentFilter);
            }
            
            // Filtrar por página
            if (pageFilter !== null) {
                filtered = filtered.filter(p => p.pagina === pageFilter);
            }
            
            // Filtrar por búsqueda
            if (searchTerm) {
                const term = searchTerm.toLowerCase();
                filtered = filtered.filter(p => 
                    p.codigo.toLowerCase().includes(term) ||
                    p.descripcion.toLowerCase().includes(term) ||
                    p.categoria.toLowerCase().includes(term)
                );
            }
            
            return filtered;
        }
        
        // Renderizar productos
        function renderProducts() {
            const container = document.getElementById('productsGrid');
            const filtered = filterProducts();
            
            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="no-results">
                        <div class="no-results-icon">🔍</div>
                        <h3>No se encontraron productos</h3>
                        <p>Intenta con otros términos de búsqueda</p>
                    </div>
                `;
                return;
            }
            
            const html = filtered.map(producto => `
                <div class="product-card">
                    <div class="product-code">${producto.codigo}</div>
                    <div class="product-desc">${producto.descripcion}</div>
                    <div class="product-meta">
                        <span class="badge badge-linea">${producto.linea}</span>
                        <span class="badge badge-categoria">${producto.categoria}</span>
                        ${producto.pagina ? `<span class="badge badge-pagina">Pág. ${producto.pagina}</span>` : ''}
                    </div>
                    <div class="add-section">
                        <input 
                            type="number" 
                            class="qty-input" 
                            id="qty-${producto.codigo}"
                            placeholder="Cantidad"
                            min="1"
                            value="${cart[producto.codigo]?.cantidad || ''}"
                        >
                        <button 
                            class="add-btn" 
                            onclick="addToCart('${producto.codigo}')"
                        >
                            ${cart[producto.codigo] ? '✓ Actualizar' : '+ Agregar'}
                        </button>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = html;
        }
        
        // Agregar al carrito
        function addToCart(codigo) {
            const input = document.getElementById(`qty-${codigo}`);
            const cantidad = parseInt(input.value);
            
            if (!cantidad || cantidad < 1) {
                alert('⚠️ Por favor ingresa una cantidad válida');
                return;
            }
            
            const producto = PRODUCTOS_DB.find(p => p.codigo === codigo);
            if (!producto) return;
            
            cart[codigo] = {
                codigo: producto.codigo,
                descripcion: producto.descripcion,
                cantidad: cantidad
            };
            
            saveCart();
            renderCart();
            renderProducts(); // Re-render para actualizar botones
            
            // Feedback visual
            input.style.borderColor = '#28a745';
            setTimeout(() => {
                input.style.borderColor = '#e0e0e0';
            }, 500);
        }
        
        // Actualizar cantidad en carrito
        function updateCartQty(codigo, delta) {
            if (!cart[codigo]) return;
            
            cart[codigo].cantidad += delta;
            
            if (cart[codigo].cantidad < 1) {
                removeFromCart(codigo);
                return;
            }
            
            saveCart();
            renderCart();
        }
        
        // Eliminar del carrito
        function removeFromCart(codigo) {
            delete cart[codigo];
            saveCart();
            renderCart();
            renderProducts(); // Re-render para actualizar botones
        }
        
        // Renderizar carrito
        function renderCart() {
            const itemsContainer = document.getElementById('cartItems');
            const items = Object.values(cart);
            
            if (items.length === 0) {
                itemsContainer.innerHTML = `
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <p>Tu pedido está vacío</p>
                        <p style="font-size: 12px; margin-top: 10px;">Agrega productos desde el catálogo</p>
                    </div>
                `;
                updateStats();
                return;
            }
            
            const html = items.map(item => `
                <div class="cart-item">
                    <div class="cart-item-code">${item.codigo}</div>
                    <div class="cart-item-desc">${item.descripcion}</div>
                    <div class="cart-item-qty">
                        <div class="qty-controls">
                            <button class="qty-btn" onclick="updateCartQty('${item.codigo}', -1)">-</button>
                            <span class="qty-display">${item.cantidad}</span>
                            <button class="qty-btn" onclick="updateCartQty('${item.codigo}', 1)">+</button>
                        </div>
                        <span class="remove-btn" onclick="removeFromCart('${item.codigo}')">Eliminar</span>
                    </div>
                </div>
            `).join('');
            
            itemsContainer.innerHTML = html;
            updateStats();
        }
        
        // Actualizar estadísticas
        function updateStats() {
            const items = Object.values(cart);
            const totalLines = items.length;
            const totalQty = items.reduce((sum, item) => sum + item.cantidad, 0);
            
            document.getElementById('totalLines').textContent = totalLines;
            document.getElementById('totalQty').textContent = totalQty;
            
            // Habilitar/deshabilitar botones
            const hasItems = totalLines > 0;
            document.getElementById('generateBtn').disabled = !hasItems;
            document.getElementById('clearBtn').disabled = !hasItems;
        }
        
        // Limpiar carrito
        function clearCart() {
            if (!confirm('⚠️ ¿Estás seguro de que deseas limpiar todo el pedido?')) {
                return;
            }
            
            cart = {};
            saveCart();
            renderCart();
            renderProducts();
        }
        
        // Generar archivo Excel
        function generateExcel() {
            const clientCode = document.getElementById('clientCode').value.trim();
            const orderDate = document.getElementById('orderDate').value;
            
            // Validaciones
            if (!clientCode) {
                alert('⚠️ Por favor ingresa el número SIMAN del cliente');
                document.getElementById('clientCode').focus();
                return;
            }
            
            if (!/^\d+$/.test(clientCode)) {
                alert('⚠️ El número SIMAN debe contener solo números');
                document.getElementById('clientCode').focus();
                return;
            }
            
            if (!orderDate) {
                alert('⚠️ Por favor selecciona la fecha del pedido');
                document.getElementById('orderDate').focus();
                return;
            }
            
            const items = Object.values(cart);
            if (items.length === 0) {
                alert('⚠️ El pedido está vacío. Agrega productos primero.');
                return;
            }
            
            // Preparar datos para Excel
            const data = items.map(item => ({
                'Código de barras': item.codigo,
                'Cantidad': item.cantidad
            }));
            
            // Crear workbook
            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Pedido");
            
            // Formatear fecha para nombre de archivo (DDMMAA)
            const dateObj = new Date(orderDate + 'T00:00:00');
            const day = String(dateObj.getDate()).padStart(2, '0');
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const year = String(dateObj.getFullYear()).slice(-2);
            const dateStr = `${day}${month}${year}`;
            
            // Nombre del archivo
            const filename = `Pedido_${clientCode}_${dateStr}.xlsx`;
            
            // Guardar datos del cliente
            saveClientData();
            
            // Descargar archivo
            XLSX.writeFile(wb, filename);
            
            // Confirmación
            alert(`✅ Pedido generado exitosamente!\n\nArchivo: ${filename}\nLíneas: ${items.length}\nCantidad total: ${items.reduce((sum, item) => sum + item.cantidad, 0)}`);
        }
        
        // Aplicar filtro de página
        function applyPageFilter() {
            const input = document.getElementById('pageInput');
            const pageNum = parseInt(input.value);
            
            if (!pageNum || pageNum < 1 || pageNum > 108) {
                alert('⚠️ Por favor ingresa un número de página válido (1-108)');
                return;
            }
            
            pageFilter = pageNum;
            renderProducts();
            showActivePageFilter();
        }
        
        // Mostrar indicador de filtro activo
        function showActivePageFilter() {
            const container = document.getElementById('activePageFilter');
            
            if (pageFilter !== null) {
                container.style.display = 'block';
                container.className = 'active-page-filter';
                container.innerHTML = `
                    <span>📄 Mostrando productos de la página ${pageFilter}</span>
                    <button class="clear-page-btn" onclick="clearPageFilter()">✕ Limpiar</button>
                `;
            } else {
                container.style.display = 'none';
            }
        }
        
        // Limpiar filtro de página
        function clearPageFilter() {
            pageFilter = null;
            document.getElementById('pageInput').value = '';
            renderProducts();
            showActivePageFilter();
        }
        
        // Event listeners
        document.getElementById('searchBox').addEventListener('input', (e) => {
            searchTerm = e.target.value;
            renderProducts();
        });
        
        document.getElementById('generateBtn').addEventListener('click', generateExcel);
        document.getElementById('clearBtn').addEventListener('click', clearCart);
        
        // Event listeners para filtro de página
        document.getElementById('pageFilterBtn').addEventListener('click', applyPageFilter);
        document.getElementById('pageInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                applyPageFilter();
            }
        });
        
        // Guardar datos del cliente en cada cambio
        ['clientCode', 'clientName', 'orderDate'].forEach(id => {
            document.getElementById(id).addEventListener('change', saveClientData);
        });
        
        // Inicialización
        initDate();
        loadCart();
        loadClientData();
        renderFilters();
        renderProducts();
        renderCart();
    </script>
</body>
</html>