//
//  ViewController.swift
//  music_room
//
//  Created by arthur on 21/10/2020.
//

import UIKit
import WebKit
import Sw

class ViewController: UIViewController, WKNavigationDelegate, View {

    var webView: WKWebView!
        
    override func loadView() {
        webView = WKWebView()
        webView.navigationDelegate = self
        view = webView
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        let url = URL(string: "https://developer.apple.com")!
        webView.load(URLRequest(url: url))
    }

}
